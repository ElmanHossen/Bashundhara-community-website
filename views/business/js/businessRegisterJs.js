
const businessForm = document.getElementById("businessForm");
businessForm.addEventListener("submit", callBusinessAjax);

function callBusinessAjax(e) {
    e.preventDefault();

    document.getElementById("nameErr").innerHTML = "";
    document.getElementById("addressErr").innerHTML = "";
    document.getElementById("phoneErr").innerHTML = "";
    document.getElementById("businessMsg").innerHTML = "";

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                document.getElementById("businessMsg").innerHTML = jsObj.message;
                businessForm.reset();
            } else {
                if (jsObj.nameErr) document.getElementById("nameErr").innerHTML = jsObj.nameErr;
                if (jsObj.addressErr) document.getElementById("addressErr").innerHTML = jsObj.addressErr;
                if (jsObj.phoneErr) document.getElementById("phoneErr").innerHTML = jsObj.phoneErr;
                if (jsObj.message) document.getElementById("businessMsg").innerHTML = jsObj.message;
            }
        }
    };

    xhr.open("POST", "../../controllers/business/businessControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "action=register" +
        "&businessName=" + encodeURIComponent(document.getElementById("businessName").value) +
        "&description=" + encodeURIComponent(document.getElementById("description").value) +
        "&address=" + encodeURIComponent(document.getElementById("address").value) +
        "&phone=" + encodeURIComponent(document.getElementById("phone").value);

    xhr.send(data);
}
