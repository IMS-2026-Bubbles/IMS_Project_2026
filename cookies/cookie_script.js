
//FROM geeksforgeeks https://www.geeksforgeeks.org/javascript/how-to-create-cookie-consent-box-using-html-css-and-javascript/
const consentBox = 
    document.getElementById("consentBox");
const acceptBtn = 
    document.querySelector(".consentButton");
const rejectBtn = 
    document.querySelector(".rejectButton");

acceptBtn.onclick = () => {
    document.cookie = "CookieBy=Scriba; max-age="
        + 60 * 60 * 24; "path=/";
    if (document.cookie) {
        consentBox.classList.add("hide");
    } else {
        alert
            ("Cookie can't be set! Please"+
              " unblock this site from the cookie"+
              " setting of your browser.");
    }
};

rejectBtn.onclick = () => {
    alert(
        "Cookies must be accepted in order to use Scriba.");
    consentBox.classList.add("hide");
};

let checkCookie = 
    document.cookie.indexOf("CookieBy=Scriba");
checkCookie !== -1 ? consentBox.classList.add("hide") :
    consentBox.classList.remove("hide");