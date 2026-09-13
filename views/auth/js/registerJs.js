// MEMBER 1 (galib) - registration AJAX
const registerForm = document.getElementById("registerForm");
registerForm.addEventListener("submit", callRegisterAjax);

function clearRegisterErrors() {
    const ids = ["userIdErr", "nameErr", "emailErr", "phoneErr", "passErr", "confirmPassErr", "successMsg"];
    for (let i = 0; i < ids.length; i++) {
        document.getElementById(ids[i]).innerHTML = "";
    }
}

function callRegisterAjax(e) {
    e.preventDefault();
    clearRegisterErrors();

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                document.getElementById("successMsg").innerHTML = jsObj.message;
                registerForm.reset();
            } else {
                if (jsObj.userIdErr) document.getElementById("userIdErr").innerHTML = jsObj.userIdErr;
                if (jsObj.nameErr) document.getElementById("nameErr").innerHTML = jsObj.nameErr;
                if (jsObj.emailErr) document.getElementById("emailErr").innerHTML = jsObj.emailErr;
                if (jsObj.phoneErr) document.getElementById("phoneErr").innerHTML = jsObj.phoneErr;
                if (jsObj.passErr) document.getElementById("passErr").innerHTML = jsObj.passErr;
                if (jsObj.confirmPassErr) document.getElementById("confirmPassErr").innerHTML = jsObj.confirmPassErr;
                if (jsObj.message) document.getElementById("userIdErr").innerHTML = jsObj.message;
            }
        }
    };

    xhr.open("POST", "../../controllers/auth/registerControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "userId=" + encodeURIComponent(document.getElementById("userId").value) +
        "&name=" + encodeURIComponent(document.getElementById("name").value) +
        "&email=" + encodeURIComponent(document.getElementById("email").value) +
        "&phone=" + encodeURIComponent(document.getElementById("phone").value) +
        "&address=" + encodeURIComponent(document.getElementById("address").value) +
        "&role=" + encodeURIComponent(document.getElementById("role").value) +
        "&pass=" + encodeURIComponent(document.getElementById("pass").value) +
        "&confirmPass=" + encodeURIComponent(document.getElementById("confirmPass").value);

    xhr.send(data);
}
