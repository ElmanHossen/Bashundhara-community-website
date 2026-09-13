// MEMBER 1 (galib) - login AJAX
const loginForm = document.getElementById("loginForm");
loginForm.addEventListener("submit", callLoginAjax);

function callLoginAjax(e) {
    e.preventDefault();

    document.getElementById("userIdErr").innerHTML = "";
    document.getElementById("passErr").innerHTML = "";
    document.getElementById("notFoundErr").innerHTML = "";

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                location.href = jsObj.redirect;
            } else {
                if (jsObj.userIdErr) document.getElementById("userIdErr").innerHTML = jsObj.userIdErr;
                if (jsObj.passErr) document.getElementById("passErr").innerHTML = jsObj.passErr;
                if (jsObj.notFoundErr) document.getElementById("notFoundErr").innerHTML = jsObj.notFoundErr;
            }
        }
    };

    xhr.open("POST", "../../controllers/auth/loginControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "userId=" + encodeURIComponent(document.getElementById("userId").value) +
        "&pass=" + encodeURIComponent(document.getElementById("pass").value);

    xhr.send(data);
}
