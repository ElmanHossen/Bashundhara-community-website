// MEMBER 4 (elman) - business approval AJAX
window.addEventListener("load", loadBusinesses);

function loadBusinesses() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                showBusinesses(jsObj.businesses);
            } else {
                document.getElementById("businessList").innerHTML = "<div class='card'><p class='error'>" + jsObj.message + "</p></div>";
            }
        }
    };

    xhr.open("POST", "../../controllers/admin/businessApprovalControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=list&status=" + encodeURIComponent(document.getElementById("statusFilter").value));
}

function showBusinesses(businesses) {
    const list = document.getElementById("businessList");

    if (businesses.length == 0) {
        list.innerHTML = "<div class='card'><p class='msg'>Nothing to show here.</p></div>";
        return;
    }

    let html = "";
    for (let i = 0; i < businesses.length; i++) {
        const b = businesses[i];
        html += "<div class='card'>";
        html += "<h2>" + b.businessName + "</h2>";
        html += "<p class='meta'>Owner: " + b.ownerName + " (" + b.ownerId + ") - " + b.ownerEmail + "</p>";
        html += "<p>" + (b.description == null ? "" : b.description) + "</p>";
        html += "<p class='msg'>" + b.address + " | " + b.phone + "</p>";
        html += "<p class='msg'>Status: <span class='status status-" + b.status + "'>" + b.status + "</span></p>";

        if (b.status != "approved") {
            html += "<button class='btn-small' onclick='setBusinessStatus(" + b.businessId + ", \"approved\")'>Approve</button> ";
        }
        if (b.status != "rejected") {
            html += "<button class='btn-small btn-danger' onclick='setBusinessStatus(" + b.businessId + ", \"rejected\")'>Reject</button>";
        }

        html += "</div>";
    }

    list.innerHTML = html;
}

function setBusinessStatus(businessId, status) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            document.getElementById("businessMsg").innerHTML = jsObj.message;

            if (jsObj.success == true) {
                loadBusinesses();
            }
        }
    };

    xhr.open("POST", "../../controllers/admin/businessApprovalControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=setStatus&businessId=" + encodeURIComponent(businessId) + "&status=" + encodeURIComponent(status));
}
