<?php

class DBconnection
{
    private $dbHost = 'localhost';
    private $dbUser = 'root';
    private $dbPass = '';
    private $dbName = 'salah_tracker';

    public function openConnection()
    {
        $connection = new mysqli($this->dbHost, $this->dbUser, $this->dbPass, $this->dbName);

        if ($connection->connect_error) {
            die("Database connection failed: " . $connection->connect_error);
        }

        $connection->set_charset("utf8mb4");
        return $connection;
    }

    // UPDATED: now also stores location fields (optional)
    public function userRegistration($connection, $tableName, $name, $email, $password, $timezone, $lat, $lng, $locationLabel)
    {
        $sql = "INSERT INTO `$tableName`
                (name, email, password, timezone, latitude, longitude, location_label)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $connection->prepare($sql);
        if (!$stmt) return 0;

        // convert empty strings to NULL for optional columns
        $tzParam = ($timezone === '') ? null : $timezone;
        $latParam = ($lat === '') ? null : $lat;
        $lngParam = ($lng === '') ? null : $lng;
        $labelParam = ($locationLabel === '') ? null : $locationLabel;

        // bind everything as strings; MySQL will convert DECIMAL columns automatically
        $stmt->bind_param("sssssss", $name, $email, $password, $tzParam, $latParam, $lngParam, $labelParam);

        $stmt->execute();
        $result = $stmt->affected_rows;

        $stmt->close();
        return $result;
    }

    public function userLogin($connection, $tableName, $email)
    {
        $sql = "SELECT id,password,timezone, latitude, longitude, location_label FROM {$tableName} WHERE email=?";

        $stmt = $connection->prepare($sql);
        if (!$stmt) return 0;

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $stmt->close();
        return $result;
    }

    public function GetUsersDetails($connection, $tableName, $userId)
    {
        $sql = "SELECT * FROM {$tableName} WHERE id=?";

        $stmt = $connection->prepare($sql);
        if (!$stmt) return 0;

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $stmt->close();
        return $result;
    }

    public function UpdateUserProfile($connection, $tableName, $userId, $needToUpdate)
    {
        $setQuery = '';
        $fieldNames = [];
        $fieldValues = [];
        $dataTypes = '';

        foreach ($needToUpdate as $fieldName => $value) {
            $modifyField = $fieldName . " = ?";
            array_push($fieldNames, $modifyField);
            array_push($fieldValues, $value);
            $dataTypes .= "s";
        }

        $setQuery = implode(',', $fieldNames);

        $sql = "UPDATE " . $tableName . " SET " . $setQuery . " WHERE id='" . $userId . "'";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param($dataTypes, ...$fieldValues);
        $stmt->execute();
        $result = $stmt->affected_rows;

        return $result;
    }

    public function CheckExistingUserEmail($connection, $tableName, $email)
    {
        $sql = "SELECT * FROM `$tableName` WHERE email=? LIMIT 1";
        $stmt = $connection->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        $stmt->close();
        if ($res->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function bindDynamic($stmt, string $types, array &$params): bool
    {
        $bind = [];
        $bind[] = $types;

        foreach ($params as $k => &$p) {
            $bind[] = &$p;
        }

        return call_user_func_array([$stmt, 'bind_param'], $bind);
    }


    public function CheckExistingSalahLog($connection, $tableName, int $userId, string $prayerDate)
    {
        $sql = "SELECT * FROM `$tableName` WHERE user_id=? AND prayer_date=? LIMIT 1";
        $stmt = $connection->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param("is", $userId, $prayerDate);
        $stmt->execute();
        $res = $stmt->get_result();

        $stmt->close();
        return $res;
    }

    public function InsertNewSalahLog($connection, $tableName, array $prayerLogs, int $userId, string $prayerDate): bool
    {
        // Base columns always present
        $columns = ['user_id', 'prayer_date'];
        $placeholders = ['?', '?'];
        $types = 'is';
        $params = [$userId, $prayerDate];

        foreach ($prayerLogs as $key => $value) {
            $columns[] = $key;
            $placeholders[] = '?';

            if (str_ends_with($key, '_Status')) {
                $types .= 's';
                $params[] = (string)$value;
            } else {
                $types .= 'i';
                $params[] = (int)$value;
            }
        }

        $sql = "INSERT INTO `$tableName` (" . implode(', ', $columns) . ")
            VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $connection->prepare($sql);
        if (!$stmt) return false;

        $this->bindDynamic($stmt, $types, $params);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function UpdateExistingSalahLog($connection, $tableName, array $prayerLogs, int $userId, string $prayerDate, int $id): bool
    {
        if (empty($prayerLogs)) return true; // nothing to update

        $setParts = [];
        $types = '';
        $params = [];

        foreach ($prayerLogs as $key => $value) {
            $setParts[] = "`$key` = ?";

            if (str_ends_with($key, '_Status')) {
                $types .= 's';
                $params[] = (string)$value;
            } else {
                $types .= 'i';
                $params[] = (int)$value;
            }
        }

        // WHERE user_id=?, prayer_date=?, id=?
        $types .= 'isi';
        $params[] = $userId;
        $params[] = $prayerDate;
        $params[] = $id;

        $sql = "UPDATE `$tableName`
            SET " . implode(', ', $setParts) . "
            WHERE user_id = ? AND prayer_date = ? AND id = ?";

        $stmt = $connection->prepare($sql);
        if (!$stmt) return false;

        $this->bindDynamic($stmt, $types, $params);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function WeeklyActivities($connection, int $userId, string $weekStart, string $weekEnd)
    {
        $sql = "SELECT prayer_date,
               Fajr_Status, Dhuhr_Status, Asr_Status, Maghrib_Status, Isha_Status,
               Fajr_Fard, Fajr_Sunnah, Fajr_Nafl,
               Dhuhr_Fard, Dhuhr_Sunnah, Dhuhr_Nafl,
               Asr_Fard, Asr_Sunnah, Asr_Nafl,
               Maghrib_Fard, Maghrib_Sunnah, Maghrib_Nafl,
               Isha_Fard, Isha_Sunnah, Isha_Nafl
        FROM salah_log
        WHERE user_id = ?
          AND prayer_date BETWEEN ? AND ?
        ORDER BY prayer_date";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param("iss", $userId, $weekStart, $weekEnd);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        return $res;
    }

    public function ReturnAllSalahLogOfUSer($connection, $tableName, $userId)
    {
        $sql = "SELECT * FROM `$tableName` WHERE user_id=? ORDER BY prayer_date DESC";

        $stmt = $connection->prepare($sql);
        if (!$stmt) return 0;

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $stmt->close();
        return $result;
    }

    public function closeConnection($connection)
    {
        $connection->close();
    }
}
