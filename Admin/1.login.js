function validateLogin() {

    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value;

    if (username === "" && password === "") {
        alert("Please enter username and password.");
        return false;
    }

    if (username === "") {
        alert("Please enter username.");
        return false;
    }

    if (password === "") {
        alert("Please enter password.");
        return false;
    }

    return true;
}