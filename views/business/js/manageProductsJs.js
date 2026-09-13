
window.addEventListener("load", loadMyProducts);

const productForm = document.getElementById("productForm");
productForm.addEventListener("submit", saveProduct);

function loadMyProducts() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                showMyProducts(jsObj.products);
            } else {
                document.getElementById("productList").innerHTML = "<p class='error'>" + jsObj.message + "</p>";
            }
        }
    };

    xhr.open("POST", "../../controllers/business/productControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=list");
}

function showMyProducts(products) {
    const list = document.getElementById("productList");

    if (products.length == 0) {
        list.innerHTML = "<p class='msg'>No products yet. Add your first one above.</p>";
        return;
    }

    let html = "<table><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Action</th></tr>";

    for (let i = 0; i < products.length; i++) {
        const p = products[i];
        html += "<tr>";
        html += "<td>" + p.productName + "</td>";
        html += "<td>" + (p.pCategoryName == null ? "-" : p.pCategoryName) + "</td>";
        html += "<td>Tk " + p.price + "</td>";
        html += "<td>" + p.stock + "</td>";
        html += "<td>";
        html += "<button class='btn-small' onclick=\"editProduct(" + p.productId + ", '" + escapeText(p.productName) + "', '" + escapeText(p.description == null ? '' : p.description) + "', " + p.price + ", " + p.stock + ", " + p.pCategoryId + ")\">Edit</button> ";
        html += "<button class='btn-small btn-danger' onclick='removeProduct(" + p.productId + ")'>Delete</button>";
        html += "</td>";
        html += "</tr>";
    }

    html += "</table>";
    list.innerHTML = html;
}

function escapeText(text) {
    return text.replace(/\\/g, "\\\\").replace(/'/g, "\\'").replace(/\r?\n/g, " ");
}

function editProduct(productId, name, description, price, stock, categoryId) {
    document.getElementById("productId").value = productId;
    document.getElementById("productName").value = name;
    document.getElementById("description").value = description;
    document.getElementById("price").value = price;
    document.getElementById("stock").value = stock;
    document.getElementById("pCategoryId").value = categoryId;
    document.getElementById("submitBtn").value = "Save changes";
    window.scrollTo(0, 0);
}

function resetForm() {
    productForm.reset();
    document.getElementById("productId").value = "0";
    document.getElementById("submitBtn").value = "Add product";
    document.getElementById("productMsg").innerHTML = "";
}

function saveProduct(e) {
    e.preventDefault();

    const ids = ["nameErr", "categoryErr", "priceErr", "stockErr", "productMsg"];
    for (let i = 0; i < ids.length; i++) {
        document.getElementById(ids[i]).innerHTML = "";
    }

    const productId = document.getElementById("productId").value;
    const action = (productId == "0") ? "add" : "update";

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                document.getElementById("productMsg").innerHTML = jsObj.message;
                resetForm();
                loadMyProducts();
            } else {
                if (jsObj.nameErr) document.getElementById("nameErr").innerHTML = jsObj.nameErr;
                if (jsObj.categoryErr) document.getElementById("categoryErr").innerHTML = jsObj.categoryErr;
                if (jsObj.priceErr) document.getElementById("priceErr").innerHTML = jsObj.priceErr;
                if (jsObj.stockErr) document.getElementById("stockErr").innerHTML = jsObj.stockErr;
                if (jsObj.message) document.getElementById("productMsg").innerHTML = jsObj.message;
            }
        }
    };

    xhr.open("POST", "../../controllers/business/productControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "action=" + action +
        "&productId=" + encodeURIComponent(productId) +
        "&productName=" + encodeURIComponent(document.getElementById("productName").value) +
        "&pCategoryId=" + encodeURIComponent(document.getElementById("pCategoryId").value) +
        "&description=" + encodeURIComponent(document.getElementById("description").value) +
        "&price=" + encodeURIComponent(document.getElementById("price").value) +
        "&stock=" + encodeURIComponent(document.getElementById("stock").value);

    xhr.send(data);
}

function removeProduct(productId) {
    if (!confirm("Remove this product?")) {
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                loadMyProducts();
            } else {
                alert(jsObj.message);
            }
        }
    };

    xhr.open("POST", "../../controllers/business/productControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=delete&productId=" + encodeURIComponent(productId));
}
