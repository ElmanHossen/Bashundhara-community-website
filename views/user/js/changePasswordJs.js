const passwordForm = document.getElementById("passwordForm");
passwordForm.addEventListener("submit", callPasswordAjax);

function callPasswordAjax(e) {
    e.preventDefault();

    document.getElementById("oldPassErr").innerHTML = "";
    document.getElementById("newPassErr").innerHTML = "";
    document.getElementById("confirmPassErr").innerHTML = "";
    document.getElementById("passMsg").innerHTML = "";

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                document.getElementById("passMsg").innerHTML = jsObj.message;
                passwordForm.reset();
            } else {
                if (jsObj.oldPassErr) document.getElementById("oldPassErr").innerHTML = jsObj.oldPassErr;
                if (jsObj.newPassErr) document.getElementById("newPassErr").innerHTML = jsObj.newPassErr;
                if (jsObj.confirmPassErr) document.getElementById("confirmPassErr").innerHTML = jsObj.confirmPassErr;
            }
        }
    };

    xhr.open("POST", "../../controllers/auth/passwordControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "oldPass=" + encodeURIComponent(document.getElementById("oldPass").value) +
        "&newPass=" + encodeURIComponent(document.getElementById("newPass").value) +
        "&confirmPass=" + encodeURIComponent(document.getElementById("confirmPass").value);

    xhr.send(data);
}
