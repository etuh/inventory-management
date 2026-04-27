# Company Equipment Tracking System - User Manual

Welcome to the **Equipment Tracking System**. This application is designed exclusively for recording and managing internal company equipment, tools, and accessories. It provides a centralized way to track exactly who has been assigned a specific piece of equipment, their department, the technical specifications of the item, and its current physical condition.

---

## 1. Getting Started

### 1.1 Creating an Account
1. Open your web browser and navigate to the application's URL.
2. Click on the **Register** link located on the welcome page.
3. Fill in your **Name**, **Email Address**, and a secure **Password**.
4. Confirm your password and click **Register**.
5. You will be authenticated and redirected to the Dashboard.

### 1.2 Logging In
1. Navigate to the application's URL.
2. Click on **Log In**.
3. Enter your registered email address and password.
4. Click **Log In**.

---

## 2. The Dashboard

The Dashboard provides a high-level view of your company's equipment fleet:
- **Equipment Overview:** See the total number of tools and assets currently tracked.
- **Assignment Status:** View a breakdown of equipment by their deployment state (e.g., **Available** in the IT closet, **Assigned** to an employee, in **Maintenance**, or **Retired**).
- **Condition Metrics:** Identify the health of your equipment with charts displaying overall conditions (**New**, **Ok**, or **Outdated**).

---

## 3. Configuration (Management)

Before you begin logging specific equipment, use the **Management** section to define the structure of your tools and your organization.

### 3.1 Managing Equipment Categories (Inventories)
Group your company assets into high-level categories (e.g., *IT Hardware*, *Power Tools*, *Office Furniture*).
- **Create:** Click "Add", specify the category name, and save.

### 3.2 Managing Device Types & Specs
"Devices" represent the specific models or types of equipment you own (e.g., *Laptops*, *Drills*, *Monitors*).
- **Add Device Type:** Give the device a name and place it under an existing category.
- **Specification Fields:** Define the exact specs you want to track for this type of equipment (e.g., "Processor", "RAM", "Voltage", "Weight"). Every time you log a piece of equipment of this type, the system will prompt you for these specific details.
- **Operating System / Additional Data:** Enable tracking for OS details or other custom network data (MAC Address, IP) if applicable.

### 3.3 Departments & Assignees
To accurately track who has what:
- **Departments:** Create a list of your company's departments (e.g., *Engineering*, *Marketing*, *Field Ops*).
- **Assignees (External/Internal Users):** Keep a registry of employees, contractors, or external users who can receive equipment, so you always know who is accountable for a tool.

---

## 4. Tracking Equipment & Tools (Inventory)

Once your departments and device types are configured, navigate to the **Inventory** section to record individual pieces of equipment, tools, and accessories.

### 4.1 Recording a New Piece of Equipment
1. Click the **New Asset** button.
2. **Classification:** Select the broad category and the specific equipment type (Device).
3. **Core Details:** Enter the **Name**, **Serial Number**, **Brand/Model**, **Model No**, and **Purchase Date**.
4. **Current State:**
   - **Condition:** Record if the item is *New*, *Ok*, or *Outdated*.
   - **Status:** Mark the item as *Available*, *Assigned*, *Maintenance*, or *Retired*.
5. **Assignment:** If you are handing this tool directly to someone, select the **Assigned User** (or custom **Assignee**) and note their **Department**. This ensures complete accountability.
6. **Specs & Accessories:**
   - Fill in the required **Specifications** (e.g., RAM, Processor) prompted by the system.
   - List any **Accessories** that are bundled with this equipment (e.g., exactly which mouse, charger, or carrying case the user received).
7. Click **Save** to add the equipment to the registry.

### 4.2 Updating Assignments and Condition
When an employee returns a tool, hands it to someone else, or if the equipment gets damaged:
1. Locate the equipment in the list and click **Edit**.
2. **Reassign:** Change the **Assigned User** or **Department** if it has moved to a new employee.
3. **Update Condition:** If the item was returned damaged, change its condition from *Ok* to *Outdated* or change its status to *Maintenance*.
4. Click **Save Changes** to maintain an accurate, up-to-date record.

### 4.3 Retiring or Deleting Equipment
- If equipment is lost, sold, or destroyed, you can mark its Status as *Retired*.
- If you made a data-entry error, you can click the **Delete** button to permanently remove the record from the company database.

---

## 5. User Profile
1. Click your name in the corner of the application to access your **Profile**.
2. Update your personal name, email address, or change your password to keep the system secure.
