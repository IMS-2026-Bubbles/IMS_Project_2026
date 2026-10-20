//FROM geeksforgeeks https://www.geeksforgeeks.org/javascript/how-to-create-cookie-consent-box-using-html-css-and-javascript/
const consentBox = document.getElementById("consentBox");
const acceptBtn = document.querySelector(".consentButton");
const rejectBtn = document.querySelector(".rejectButton");

//checks if cookie is already set, hides banner
if (document.cookie.includes("CookieBy=")) {
    consentBox.classList.add("hide");
} else {
    // to mitigate the banner flashing on the page when loading the page
  consentBox.classList.add("show");
}

//for how long the cookies will be saved
const ONE_DAY = 60 * 60 *24

function saveChoice(value) {
  //checks if the cookies have been accepted already
  document.cookie = `CookieBy=${value}; max-age=${ONE_DAY}; path=/; SameSite=Lax`;
  consentBox.classList.add("hide");
}

acceptBtn.onclick = () => {
    saveChoice("Scriba")
    if (document.cookie) {
        //if the cookie was set
        consentBox.classList.add("hide");
    } else {
        // if something went wrong
        alert
            ("Cookie can't be set! Please"+
              " unblock this site from the cookie"+
              " setting of your browser.");
    }
};

rejectBtn.onclick = () => {
    //just accept necessary cookies
    saveChoice("rejected")
    //alert removed since necessary cookies are accepted
    consentBox.classList.add("hide");
};