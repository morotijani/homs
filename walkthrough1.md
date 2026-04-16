# LuxeStay Hotel Management System - Project Walkthrough

The Hotel Management System (HMS) has been fully built and successfully deployed locally using PHP, MySQL (PDO), and Bootstrap 5. This final year project features a robust role-based architecture, safe database connections, secure password hashing, and modern UI/UX design.

## Project Structure & Architecture

The system was organized deliberately to be modular and extremely easy for a student to trace and explain to examiners:

*   **`index.php`**: The dynamic landing page summarizing available hotel inventory.
*   **`includes/`**: Central engines. Contains `db.php` (PDO Database Connection), `auth.php` (Session/Role check engine), and unified `header.php`/`footer.php`.
*   **`assets/`**: Contains the custom `style.css` which works alongside Bootstrap to deliver a gorgeous "vibrant-glassmorphic" feel.
*   **Role-Based Modules**:
    *   **`/admin/`**: The highest tier. Unrestricted CRUD on Rooms, Users, Guests. Contains a live revenue and occupancy statistical dashboard.
    *   **`/receptionist/`**: The operational tier. Controls booking states (Pending -> Confirmed -> Checkout) and final invoice/billing processing.
    *   **`/customer/`**: The end-user tier. Lets public users book rooms dynamically, ensuring dates are valid and computing the total required invoice dynamically.

## Pre-Loaded Dummy Accounts

Your DB (`homs_db`) was populated successfully with test accounts that can be used out of the box for a demo or viva. 

**All accounts share the password: `password123`**

1.  **Admin Account:** `admin` (Has full access to `/admin/*`)
2.  **Receptionist:** `receptionist` (Has access to `/receptionist/*`)
3.  **Customer:** `johndoe` or `janedoe` (Has access to `/customer/*` and `book.php`)

## Verification Steps Performed

### 1. Database Implementation 
✔️ Processed the MySQL schema translating the ERD accurately.
✔️ Injected tables with automatic `created_at` / `updated_at` timestamps correctly binding Foreign Keys.

### 2. Security Layer
✔️ **SQL Injection Guard:** Enforced via PDO array execution across **all** CRUD operations.
✔️ **XSS Guard:** All data outputs are sanitized natively (e.g. `htmlspecialchars()`).
✔️ **Access Guard:** Configured the `requireRole($role)` router which destroys malicious jump attempts via URL parameters.

## User Flow Walkthrough

1.  **Public Site Check:** A guest visits `http://localhost/homs/`. They see a count of current rooms that are listed as "Available".
2.  **Registration Check:** They click register, where the platform requires both authentication logic (username) and detailed Guest logging (phone, email) wrapped safely inside a single MySQL transaction block.
3.  **Booking Submission:** After logging in, the customer heads to their dashboard, chooses an available room, defines a date margin (e.g., May 10th - May 15th), and the system calculates dynamic pricing.
4.  **Receptionist Lifecycle:** The Receptionist logs in. They alter the booking from `Pending` -> `Confirmed`. Upon customer departure, they click **Checkout & Bill**, entering the final payment mechanism (Cash/Card/UPI).
5.  **Admin Supervision:** The Admin refreshes their dashboard. Overall Revenue climbs by the exact transaction amount, updating real-time KPI boxes.

> [!TIP]
> **For the Student Presentation:** Start the demonstration by logging in as a new Customer, make a booking. Open a new Incognito Tab as a Receptionist to accept it. Then finally open a third tab as Admin to show the system capturing the workflow across all 3 tiers perfectly.
