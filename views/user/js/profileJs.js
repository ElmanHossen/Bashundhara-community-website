
const profileForm = document.getElementById("profileForm");
profileForm.addEventListener("submit", callProfileAjax);

function callProfileAjax(e) {
    e.preventDefault();

    document.getElementById("nameErr").innerHTML = "";
    document.getElementById("emailErr").innerHTML = "";
    document.getElementById("phoneErr").innerHTML = "";
    document.getElementById("profileMsg").innerHTML = "";

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                document.getElementById("profileMsg").innerHTML = jsObj.message;
            } else {
                if (jsObj.nameErr) document.getElementById("nameErr").innerHTML = jsObj.nameErr;
                if (jsObj.emailErr) document.getElementById("emailErr").innerHTML = jsObj.emailErr;
                if (jsObj.phoneErr) document.getElementById("phoneErr").innerHTML = jsObj.phoneErr;
            }
        }
    };

    xhr.open("POST", "../../controllers/auth/profileControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "name=" + encodeURIComponent(document.getElementById("name").value) +
        "&email=" + encodeURIComponent(document.getElementById("email").value) +
        "&phone=" + encodeURIComponent(document.getElementById("phone").value) +
        "&address=" + encodeURIComponent(document.getElementById("address").value);

    xhr.send(data);
}
