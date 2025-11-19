function showForm(formToShow) {
    document.getElementById("login-form").style.display = "none";
    document.getElementById("register-form").style.display = "none";

    document.getElementById(formToShow).style.display = "block";
}
