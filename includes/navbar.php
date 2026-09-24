 <!-- This file will be included everywhere so that the navigation bar is on every page.
  If changes are done here, they will appear everywhere then. 
  Information about navigation bar: https://getbootstrap.com/docs/5.3/components/navbar/ -->
    
<nav class="navbar navbar-expand-lg" style="background-color: #e3f2fd;" data-bs-theme="light">

<!-- Create special style for log out button 
 https://www.w3schools.com/howto/howto_css_outline_buttons.asp -->
<style>

.logout {
    border: 1px solid black;
    background-color: #9acaed;
    color: black;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
}
/* This is for when you "touch" the button */
.logout:hover {
    border: 1px solid black;
    background-color: #33709c;
    color: black;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
}
</style>

    
    <!-- Adds an image, here we can add scriba logo later on -->
    <div class="container-fluid"> <!-- fluid = full width-->
        <a class="navbar-brand">
        <img src="assets/placeholder.avif" alt="logo" width="30" height="40" class="d-inline-block align-text-middle">
        Scriba    
        </a>


        <!-- Add buttons for all pages -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link active" href="project_library.php">Home Page/Projects</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="leaderboard.php">Leaderboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="user_profile.php">User Profile</a> 
            </li>

            <li class="nav-item">
                <a class="nav-link active logout" href="actions/logout.php">Log out</a> 
            </li>
        </ul>        

    </div>
</nav>