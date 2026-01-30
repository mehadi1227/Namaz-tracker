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