# Book-Review-Web-Application

## Overview

This project is a Book Review Web Application developed as part of the CS340 Database Systems course at Oregon State University. It allows users to review books, track their reading history, and search for books based on various factors such as genre, author, and rating. Admin users can manage the book database and moderate reviews.

### Group Project
This project was a collaborative effort by the following team members:

Ohm Thakor, Luke Scovel, Nathaniel Wood, Kevin Nguyen

## Features

#### User Features
Book Search and Filters: Search and filter books by genre, author, and rating. 

#### Review System: 
- Rate books on a scale of 1–5 stars.  
- Write, edit, or delete reviews.  

#### Reading History:
- View the history of books read and reviewed. 
- Create and manage reading lists (e.g., wishlists, genres). 

#### Detailed Book Information: 
View author details, genre, and all user reviews for a book.

### Admin Features
- Add or remove books from the database.
- Moderate reviews (delete inappropriate or irrelevant reviews).
Requirements

#### Functional Requirements
- Users can only have one review per book.
- Authors cannot review their own books.
- Reading lists are limited to 100 books.
- Books must have a unique listing in the database.

## Database Design

#### Entities and Attributes:
- User: Tracks user details (username, role, date joined).
- Book: Stores book information (title, author, genre, avg_rating).
- Author: Tracks author details (first name, last name).
- Reading List: Manages user-specific lists of books.
- Review: Stores user reviews with ratings and timestamps.

#### Triggers
update_avg_rating: Updates the average rating of a book when a review is added.  
update_avg_rating_on_update: Updates the average rating when a review is modified.  
update_avg_rating_on_delete: Updates the average rating when a review is deleted.  

## Application Implementation

#### Technologies Used
- HTML/PHP: For building the user interface and handling backend logic.  
- CSS: For styling the web pages (e.g., user-friendly forms, tables, buttons).  
- JavaScript: For dynamic interactions like form validation and responsive UI elements.  
- MySQL: For storing and managing application data.  
- Triggers and Stored Procedures: To ensure data integrity and automate updates.
  
### Key Pages  

#### Main Page:  
- Displays book details, including author, genre, and reviews.
- Allows users to add reviews and manage their reading lists.
<img width="500" alt="Screenshot 2024-12-13 at 4 45 09 PM" src="https://github.com/user-attachments/assets/6f3e58b0-fb6d-487e-8bae-1f82f6dbbef3" />
<img width="500" alt="Screenshot 2024-12-13 at 4 45 30 PM" src="https://github.com/user-attachments/assets/8809ddbd-b0b3-4c4c-a401-2c7fb6fca4ee" />



  
#### Login Page:
- Provides access to the application with user authentication.
- Admin users log in with separate credentials for database management.
<img width="500" alt="Screenshot 2024-12-13 at 4 44 27 PM" src="https://github.com/user-attachments/assets/3b71e241-0b23-4975-a277-023edc3cc594" />
  
#### User Profile Page:
- Displays the user's reading list and reviews.
- Allows editing/deleting reviews and managing lists.

### Login Credentials:
#### Regular User:
  Username: john_doe  
  Password: password1
  
#### Admin User:
  Username: admin_user  
  Password: password2
  
### Performing CRUD Operations:
- Create: Add books to reading lists or write reviews.
- Read: Search/filter books and view details or reviews.
- Update: Modify reviews or manage reading lists.
- Delete: Remove reviews or clear books from reading lists.

