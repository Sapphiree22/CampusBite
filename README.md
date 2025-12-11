CampusBite: One Tap, One Bite 
A Smart Food Ordering and Seat Reservation App for University Canteens
CampusBite is a mobile application designed to solve the common problem of long lines, crowded spaces, 
and wasted time students face in the school canteen during rush hours. By enabling students to pre-order
meals and reserve seats, CampusBite aims to provide a more efficient, organized, and comfortable canteen experience.

Key Features
CampusBite provides solutions for both students (Primary Users) and canteen management (Secondary Users)

Student Features (Primary Users)

Pre-Order Function: Allows students to order meals ahead of time, before their break starts, to skip the queue.
Seat Reservation: Students can check real-time availability and reserve a table inside the canteen.
Digital Menu Display: Users can view all available dishes and their prices in one clear interface.

Management Features (Secondary Users)

SuperAdmin & Analytics Portal: A dedicated interface for management with full system oversight.
Food Popularity Analytics: View which items are most-to-least ordered daily.
Logs History: Records all major system actions (reservations, menu changes, updates) for auditing and transparency.
Menu Management: Admins can easily add, edit, or delete menu items at any time.
Cut-off Time Toggle: Allows admins to control the opening and closing times of the system.
Meal Consumption Update: The system can track when a meal is consumed, aiding in inventory and meal usage tracking

Project Structure and Technology
This project involves Front-End Development, Back-End Development, Database Design, and HCI/UX Research.

Component,Responsibility,Team Member Role
Front-End,  Mobile App UI/UX (Sign-in, Home, Menu, Cart, Tables, Profile),  Kathleen Belda, Kaira Comaling 
Back-End, n Application Logic, API Handling, Jhon Clark Caritan 
Database, Entity-Relationship Management (Orders, Reservations, Users, Menu Items) ,James Bryle de los Santos 
UX/Research,User experience and interface design ,Donalen Alvarado, Rosejenel Garcia 

Entity-Relationship Diagram (ERD)
The database design is structured around key entities like Users, Orders, Reservation, Food_Item, and Item_Variant.
<img width="813" height="536" alt="image" src="https://github.com/user-attachments/assets/d0103496-fd19-4e16-9995-54f19a66c5b2" />

Getting Started
These instructions will get your project running locally using Visual Studio, GitHub, and your local MySQL server.

Prerequisites
Visual Studio (Community/Professional/Enterprise)
MySQL Server (Local installation)
MySQL for Visual Studio and MySQL Connector/NET (Required for connecting the IDE and your app to the database)

A GitHub account and Git installed.
1. Database Setup
Create Database: In your local MySQL server (using MySQL Workbench or the command line), create a new database named CampusBiteDB.
Schema Installation: Run the SQL script located in the Database/schema.sql folder (create this folder in your repo if it doesn't exist) to set up all tables according to the ERD.
Visual Studio Connection:
In Visual Studio, open the Server Explorer window.
Right-click on Data Connections and select Add Connection.
Choose MySQL Database as the Data Source and enter your local server details (hostname, user, password, and the CampusBiteDB database name).

2. Project Installation and Cloning
Clone the Repository:
In Visual Studio, go to the Git menu and select Clone Repository.
Enter the GitHub URL for your CampusBite project.
Open Solution: Open the CampusBite.sln solution file in Visual Studio.
Install NuGet Packages: Ensure all necessary packages (especially MySql.Data and any Entity Framework packages) are installed. Right-click on the solution in the Solution Explorer and select Restore NuGet Packages.
Update Connection String: Locate the application's configuration file (e.g., App.config, Web.config, or a separate configuration class) and update the connectionString to match your local MySQL credentials.

3. Running the Application
Set Startup Project: Right-click on the main project (e.g., the mobile app or web API) in the Solution Explorer and select Set as Startup Project.
Run: Press F5 or the Start button in Visual Studio to build and run the application.
