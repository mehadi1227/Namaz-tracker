const toggleProfileOption = () => {
  const optionBar = document.getElementById('profile_option_container');
  optionBar.style.display = (optionBar.style.display === 'block') ? 'none' : 'block';
};

const date = new Date();

const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
const months = ["January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"];

const formattedDate = `${days[date.getDay()]}, ${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;

const navcenter = document.getElementById('nav_center');
navcenter.textContent = formattedDate;

function LogOut()
{
  const xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState !== 4) return;

    if (this.status === 200) {
      alert("Logged out successfully.");
      window.location.href = "../../Authentication/Login/";
    } else {
      alert("Failed to log out:", this.responseText);
    }
  };

  xhttp.open("POST", "../../api/Logout.php", true);
  xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhttp.send("logout=true");
}