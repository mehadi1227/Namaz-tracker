const SaveToSalahLog = (event) => {
    event.preventDefault();

    const form = event.currentTarget;

    const dateVal = (document.getElementById("log_date")?.value || "").trim();

    if (!dateVal) {
        // console.error("Date is empty. Select a date first.");
        alert("Please select a date.");
        return;
    }


    const hidden = document.getElementById("prayer_date");
    if (hidden) hidden.value = dateVal;

    const fd = new FormData(form);

    if (fd.has("prayer_date")) fd.delete("prayer_date");
    fd.append("prayer_date", dateVal);

    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState !== 4) return;

        if (this.status === 200) {
            alert("Saved: " + this.responseText);
        } else {
            alert("Error saving Salah log: " + this.responseText);
        }
    };

    xhttp.open("POST", "../api/saveSalahLog.php", true);
    xhttp.send(fd);
}

function GetTodaysLog()
{
    const dateVal = new Date().toISOString().split('T')[0];

    if (!dateVal) {
        return;
    }

    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState !== 4) return;

        if (this.status === 200) {
            const resp = JSON.parse(this.responseText);
            console.log("Retrieved Salah log:", resp);
            PutValuesOfSalahLog(resp);
        } else {
            alert("Error retrieving Salah log: " + this.responseText);
        }
    };

    xhttp.open("POST", `../api/retriveFromSalahLog.php`, true);
    xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhttp.send(`prayer_date=${encodeURIComponent(dateVal)}`);
}

GetTodaysLog();

function PutValuesOfSalahLog(log)
{
    const totalPrayed = document.getElementById('total_prayed');
    const totalMissed = document.getElementById('total_missed');
    const summaryText = document.getElementById('summary_text');

    if(log && typeof log === 'object') {

        const keyNames = ["Asr_Fard","Asr_Nafl","Asr_Status","Asr_Sunnah","Dhuhr_Fard",
            "Dhuhr_Nafl","Dhuhr_Status","Dhuhr_Sunnah","Fajr_Fard","Fajr_Nafl","Fajr_Status",
            "Fajr_Sunnah","Isha_Fard","Isha_Nafl","Isha_Status","Isha_Sunnah","Maghrib_Fard",
            "Maghrib_Nafl","Maghrib_Status","Maghrib_Sunnah"]
            let prayed = 0;
            let missed = 0;

            keyNames.forEach(key => {
                if(log[key] === 'Missed')
                {
                    missed +=1;
                }else if(log[key] ==='on time' || log[key] === 'Late'){
                    prayed +=1;
                }
            })


        totalPrayed.textContent = prayed || '0';
        totalMissed.textContent = missed || '0';
        summaryText.textContent = 'No summary available.';
    } else {
        totalPrayed.textContent = '0';
        totalMissed.textContent = '0';
        summaryText.textContent = "Select your rakats and press “Save log”.";
    }
}
