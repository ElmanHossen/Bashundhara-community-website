# Community Business Portal

Web Technology project — PHP MVC + plain AJAX (XMLHttpRequest), exactly the pattern
from the sample project in `wt_A_sample_proj`.

## Setup

1. Copy this whole folder into `xampp/htdocs/` (or `/Applications/XAMPP/htdocs/`).
2. Start Apache + MySQL.
3. Open phpMyAdmin → Import → run `database/schema.sql`. This creates the
   `community_portal` database, all tables and the sample data.
4. Visit `http://localhost/<folder-name>/`.

### Test accounts (password is in the schema)

| User id | Password | Role |
|---|---|---|
| admin1 | admin123 | admin |
| galib | 1234 | community member |
| shop1 | 1234 | business owner |

## How it works

Same three layers as the sample project:

- **models/** — `mysqli` procedural code with prepared statements. Every function
  calls `dbConnection()` from `dbConnect.php` and returns an array, a row, or a bool.
- **controllers/** — read `$_POST`, validate, call a model function, then
  `header('Content-Type: application/json'); echo json_encode($response);`
- **views/** — the `.php` page prints the layout, the matching `js/*.js` file sends an
  `XMLHttpRequest` to a controller and paints the JSON into the DOM. No page reload,
  no jQuery, no `fetch()`.

Every AJAX call follows the same shape as the `day 6/ajax/ajaxPostReq.html` example:

```js
const xhr = new XMLHttpRequest();
xhr.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
        const jsObj = JSON.parse(this.responseText);
        // ...paint the page
    }
};
xhr.open("POST", "../../controllers/<module>/<file>.php", true);
xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
xhr.send("action=list&key=" + encodeURIComponent(value));
```

Controllers that do more than one thing switch on a `action` field in the POST body.

---

## File ownership — this is what prevents merge conflicts

Nobody edits a file that is not on their own list. Because each member owns whole
folders, git never has to merge the same file twice.

### Shared — frozen on `main` (nobody edits these on a personal branch)

```
index.php
database/schema.sql
models/dbConnect.php
views/css/style.css
views/partials/nav.php
README.md
```

If one of these genuinely has to change, say so in the group chat and let one person
change it on `dev`. Everyone else pulls afterwards.

### Member 1 — galib — Authentication & User Module

```
models/usersModel.php
controllers/auth/registerControl.php
controllers/auth/loginControl.php
controllers/auth/profileControl.php
controllers/auth/passwordControl.php
views/auth/login.php
views/auth/register.php
views/auth/logout.php
views/auth/js/loginJs.js
views/auth/js/registerJs.js
views/user/userDashboard.php
views/user/profile.php
views/user/changePassword.php
views/user/js/profileJs.js
views/user/js/changePasswordJs.js
views/css/auth.css
```

Covers: registration, login/logout, role-based authentication and authorization,
user profile, password management, user dashboard, session management, `users` table.

### Member 2 — amit — Community & Social Module

```
models/postsModel.php
controllers/community/postControl.php
controllers/community/commentControl.php
controllers/community/likeControl.php
controllers/community/reportControl.php
views/community/feed.php
views/community/createPost.php
views/community/myPosts.php
views/community/postDetails.php
views/community/js/feedJs.js
views/community/js/createPostJs.js
views/community/js/myPostsJs.js
views/community/js/postDetailsJs.js
views/css/community.css
```

Covers: community feed, create/edit/delete posts, post categories, comments and
replies, likes/reactions, report system, community search.

### Member 3 — fahim — Business & Marketplace Module

```
models/businessModel.php
controllers/business/businessControl.php
controllers/business/productControl.php
controllers/business/cartControl.php
controllers/business/orderControl.php
views/business/businessRegister.php
views/business/businessDashboard.php
views/business/manageProducts.php
views/business/businessOrders.php
views/business/js/businessRegisterJs.js
views/business/js/manageProductsJs.js
views/business/js/businessOrdersJs.js
views/marketplace/shop.php
views/marketplace/cart.php
views/marketplace/myOrders.php
views/marketplace/js/shopJs.js
views/marketplace/js/cartJs.js
views/marketplace/js/myOrdersJs.js
views/css/business.css
```

Covers: business registration/profile, business dashboard, product management,
product categories, product search/filter, shopping cart, order placement and
management.

### Member 4 — elman — Admin & Website Management

```
models/adminModel.php
controllers/admin/userManageControl.php
controllers/admin/businessApprovalControl.php
controllers/admin/moderationControl.php
controllers/admin/reportControl.php
controllers/admin/noticeControl.php
controllers/admin/homepageControl.php
controllers/admin/statsControl.php
views/home.php
views/admin/adminDashboard.php
views/admin/manageUsers.php
views/admin/manageBusinesses.php
views/admin/moderation.php
views/admin/manageReports.php
views/admin/manageNotices.php
views/admin/manageHomepage.php
views/admin/js/adminDashboardJs.js
views/admin/js/manageUsersJs.js
views/admin/js/manageBusinessesJs.js
views/admin/js/moderationJs.js
views/admin/js/manageReportsJs.js
views/admin/js/manageNoticesJs.js
views/admin/js/manageHomepageJs.js
views/css/admin.css
```

Covers: admin dashboard, user management, business approval, post/comment
moderation, reports, notice and event management, homepage content management,
website statistics.

---

## Git workflow

The repo already has `main`, `dev`, `galib`, `amit`, `fahim`, `elman`.

### One time — put the shared base on main

One person (whoever owns the repo) does this **once**, before anyone starts:

```bash
git checkout main
# copy the project files in
git add .
git commit -m "base: mvc structure, db connect, schema, shared nav and css"
git push origin main

git checkout dev
git merge main
git push origin dev
```

Then everyone syncs their own branch off `dev`:

```bash
git checkout galib          # your own branch name
git merge dev
git push origin galib
```

### Daily work

```bash
git checkout galib
git pull origin dev         # get everyone else's merged work first
# ...edit ONLY the files on your list...
git add .
git commit -m "auth: add password change with ajax validation"
git push origin galib
```

### Getting your work into dev

Open a Pull Request on GitHub: `galib` → `dev`. One other member reviews and merges.
Never push straight to `dev` or `main`.

After a PR is merged, everyone runs `git pull origin dev` on their own branch so the
next commit starts from the latest code.

### Rules that actually stop conflicts

1. Only touch files on your own list. If you need something from another module, ask
   that person to add it — don't edit their file yourself.
2. `git pull origin dev` **before** you start working, every single time.
3. Never commit with a conflict marker (`<<<<<<<`) still in a file.
4. Don't commit `.DS_Store`, `Thumbs.db` or your editor's config. A `.gitignore` is
   included.
5. Only `dev` gets merged into `main`, and only when a milestone works end to end.

### If a conflict does happen

```bash
git checkout galib
git pull origin dev
# git says: CONFLICT in <file>
# open the file, delete the <<<<<<< ======= >>>>>>> markers, keep the correct code
git add <file>
git commit -m "resolve merge conflict in <file>"
git push origin galib
```

---

## Cross-module dependencies (read this before you merge)

These are the only places one member's code uses another's, so merge in this order:

1. **galib first.** Everything depends on the `users` table and `$_SESSION["userId"]`
   / `$_SESSION["role"]` being set by `loginControl.php`.
2. **fahim and amit next.** Both read `users` (join on `userId`) but never write to it.
   `views/marketplace/cart.php` calls `getUserById()` from galib's `usersModel.php` to
   prefill the shipping address.
3. **elman last.** `adminModel.php` reads the `posts`, `comments`, `businesses`,
   `products` and `orders` tables, so those tables have to exist first (they all come
   from `schema.sql`, so this is only about the features being testable).

The session keys everyone relies on — set once in `controllers/auth/loginControl.php`:

```php
$_SESSION["userId"]   // string, the primary key of users
$_SESSION["name"]     // display name
$_SESSION["role"]     // "admin" | "business" | "user"
```

Do not rename these.

## Integration test checklist

Run this on `dev` after every merge:

1. Register a new member → login → the nav shows Logout.
2. Write a post → it appears on the feed → like it → comment on it.
3. Register a business → login as `admin1` → approve it.
4. Log back in as the business owner → add a product.
5. As a member: search the product → add to cart → change quantity → place the order.
6. As the business owner: see the order → set it to delivered.
7. As a member: report a post → as admin, hide it → confirm it left the feed.
8. As admin: edit the homepage hero text → reload `views/home.php` and see the change.
