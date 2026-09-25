// Login form validation

function validateLogin() {

    const username =
        document.getElementById("username").value.trim();

    const password =
        document.getElementById("password").value.trim();


    if (username === "") {

        alert("Please enter your username.");

        return false;

    }


    if (password === "") {

        alert("Please enter your password.");

        return false;

    }


    return true;

}


// Login protection

function requireLogin(message) {

    if (message) {

        alert(message);

    }

    return true;

}


// Logout confirmation

function confirmLogout() {

    return confirm(
        "Are you sure you want to logout?"
    );

}


// Smooth scrolling

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const links =
        document.querySelectorAll(
            'a[href^="#"]'
        );


        links.forEach(function(link) {

            link.addEventListener(
                "click",
                function(event) {

                    const target =
                    document.querySelector(
                        this.getAttribute("href")
                    );


                    if (target) {

                        event.preventDefault();

                        target.scrollIntoView({
                            behavior: "smooth"
                        });

                    }

                }
            );

        });

    }
);