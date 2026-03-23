# Project Tasks & Progress

## ✅ Completed

* [x] Laravel project setup
* [x] Named `login` route restored for auth middleware redirects
* [x] Database migration for members
* [x] Member create page
* [x] Member list page
* [x] Member create/read routes and controller flow
* [x] Member edit/delete routes and controller flow
* [x] Member pages aligned to AdminLTE layout
* [x] Member search and pagination
* [x] Persist member search query across paginated results
* [x] Optional member photo upload on create and edit
* [x] Admin login page implemented with email-or-username sign-in
* [x] Responsive admin dashboard implemented at `/dashbord`
* [x] Auth flow tests added for login, dashboard access, and logout
* [x] Feature tests updated for guest redirects and authenticated member access
* [x] Dedicated member access-control feature tests added for guest redirects and authenticated admin page access
* [x] Protected member CRUD flows verified end to end
* [x] Dedicated authenticated member CRUD feature tests added for create, edit, update, and delete flows
* [x] Member photo replacement and deletion cleanup verified on the `public` disk
* [x] Dynamic custom fields added to member create, edit, and profile views
* [x] CSV bulk member import added to the create page
* [x] Extra CSV columns now auto-map into member dynamic fields
* [x] CSV import preview added before saving members
* [x] Duplicate detection added for CSV import review
* [x] CSV export added with selectable core and dynamic fields
* [x] Excel-compatible member export added with selectable core and dynamic fields
* [x] Forgot password flow added to admin login
* [x] Full member backup download added
* [x] Dashboard charts added for growth trends and data completeness
* [x] Bulk edit and bulk delete actions added to the members table

## 🚧 Current Focus

* [ ] UI improvements (AdminLTE polish)

## ⏭️ Next Tasks

* [ ] Polish AdminLTE UI details and spacing across member pages
* [ ] Improve error handling and validation feedback for admin workflows
* [ ] Improve Excel export to true `.xlsx` package-based output if needed

## 📌 After That

* [ ] Add role-based admin permissions
* [ ] Add audit logs for imports, exports, edits, and deletes

## 🧪 Pending Enhancements

* [ ] Error handling improvements

---

## 📝 Notes for Agents

* Do NOT change database schema unless necessary
* Dynamic fields are stored as JSON
* Keep UI responsive
* Follow Laravel conventions strictly

---
