# Inventory Management System - User Manual

Welcome to the **Inventory Management System**. This user manual will guide you through the features of the application, helping you track your items, manage stock levels, and navigate the dashboard effectively.

---

## 1. Getting Started

### 1.1 Creating an Account

1. Open your web browser and navigate to the application's URL (e.g., `http://localhost:8000`).
2. Click on the **Register** link located on the welcome page.
3. Fill in your **Name**, **Email Address**, and a secure **Password**.
4. Confirm your password and click **Register**.
5. Once registered, you will be automatically logged in and redirected to your Dashboard.

### 1.2 Logging In

If you already have an account:

1. Navigate to the application's URL.
2. Click on **Log In**.
3. Enter your registered email address and password.
4. (Optional) Check "Remember Me" to stay logged in on your current device.
5. Click **Log In**.

---

## 2. The Dashboard

The Dashboard is your main command center. When you log in, you will see an overview of your inventory at a glance.

- **KPI Metrics:** View high-level numbers such as Total Items, Low Stock Alerts, and Total Inventory Value.
- **Recent Activity:** A quick feed showing the latest items added or modified.
- **Alerts:** Critical notifications indicating which items are running low and need immediate restocking.
- **Sidebar Navigation:** Use the left-hand navigation menu to seamlessly switch between the Dashboard, Inventory Items, and Settings.

---

## 3. Managing Inventory

The core of the application revolves around item tracking. Navigate to the **Items** or **Inventory** section via the sidebar.

### 3.1 Viewing the Item List

The Item List provides a table view of all your inventory.

- **Sorting & Filtering:** Click on column headers (e.g., Name, SKU, Quantity, Price) to sort your items. Use the search bar to find specific products instantly.
- **Stock Indicators:** Quantities are often color-coded (e.g., green for healthy stock, red for low stock) so you can quickly identify shortages.

### 3.2 Adding a New Item

1. On the Inventory page, click the **"Add Item"** or **"New Item"** button.
2. A modal or form will slide open.
3. Enter the necessary details:
    - **Name:** The product or item name.
    - **SKU (Stock Keeping Unit):** A unique identifier for the product.
    - **Description:** A short detail about the item.
    - **Quantity:** The initial stock level.
    - **Price:** The unit cost or selling price.
4. Click **Save** or **Create**. The list will update instantly without reloading the page.

### 3.3 Editing an Existing Item

1. Locate the item you wish to modify in the table.
2. Click the **Edit** button (often represented by a pencil icon) next to the item.
3. Update the required fields in the prompt.
4. Click **Update** to save your changes.

### 3.4 Adjusting Stock Levels

When an item is sold or restocked, you must update the system:

1. Click **Edit** on the item.
2. Modify the **Quantity** field to reflect the new physical count.
3. Click **Update**.

### 3.5 Deleting an Item

1. Locate the item in the list.
2. Click the **Delete** button (often a trash can icon).
3. A confirmation prompt will appear to prevent accidental deletions. Confirm to permanently remove the item from your system.

---

## 4. User Profile & Settings

To manage your account, click on your name or avatar in the top-right corner of the screen and select **Profile** or click **Settings** in the sidebar.

### 4.1 Update Personal Information

- Under the **Profile Information** section, you can change your display name and email address. Ensure you click **Save** after making changes.

### 4.2 Change Password

- To secure your account, go to the **Update Password** section.
- Enter your Current Password, followed by your New Password.
- Confirm the New Password and click **Save**.

### 4.3 Log Out

- To securely log out of the application, click on your profile name in the top-right corner and select **Log Out**.

---

## 5. Troubleshooting & FAQ

**Q: I forgot my password, what do I do?**
A: On the login page, click "Forgot your password?". Enter your email address to receive a secure password reset link.

**Q: Why isn't the item data updating?**
A: Ensure you have a stable internet connection. If the issue persists, try refreshing the page or clearing your browser cache.

**Q: Can I restore a deleted item?**
A: Unless soft-deletes are enabled by your system administrator, deleting an item is a permanent action. Please ensure you actually want to remove the item before confirming the deletion prompt.
