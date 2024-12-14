<?php
// Database connection variables
$host = "classmysql.engr.oregonstate.edu"; // OSU MySQL server
$db = "cs340_username"; // Your database name
$user = "cs340_username"; // Your MySQL username
$pass = "password"; // Your MySQL password

// Create connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle incoming requests
$action = $_GET['action'] ?? '';

if ($action === 'addReview') {
    // Add a new review
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $conn->prepare("SELECT * FROM Book WHERE book_id = ?");
    $stmt->execute([$data['book_id']]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($book) {
        // Check if the user has already reviewed this book
        $stmt = $conn->prepare("SELECT * FROM Review WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$data['user_id'], $data['book_id']]);
        $existingReview = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingReview) {
            echo json_encode(["error" => "You have already reviewed this book."]);
        } else {
            $stmt = $conn->prepare("INSERT INTO Review (user_id, book_id, rating, review_text, date_created, date_updated) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
            try {
                $stmt->execute([$data['user_id'], $data['book_id'], $data['rating'], $data['review_text']]);
                echo json_encode(["message" => "Review added successfully!"]);
            } catch (PDOException $e) {
                echo json_encode(["error" => "Failed to add review: " . $e->getMessage()]);
            }
        }
    } else {
        echo json_encode(["error" => "Book not found."]);
    }
}

if ($action === 'editReview') {
    // Edit an existing review
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $conn->prepare("SELECT user_id FROM Review WHERE review_id = ?");
    $stmt->execute([$data['review_id']]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT role FROM User WHERE user_id = ?");
    $stmt->execute([$data['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($review && ($review['user_id'] == $data['user_id'] || $user['role'] == 'admin')) {
        $stmt = $conn->prepare("UPDATE Review SET rating = ?, review_text = ?, date_updated = CURRENT_TIMESTAMP WHERE review_id = ?");
        try {
            $stmt->execute([$data['rating'], $data['review_text'], $data['review_id']]);
            echo json_encode(["message" => "Review updated successfully!"]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Failed to update review: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["error" => "You can only edit your own reviews or you must be an admin."]);
    }
}

if ($action === 'deleteReview') {
    // Delete a review
    $review_id = $_GET['review_id'];
    $user_id = $_GET['user_id'];
    $stmt = $conn->prepare("SELECT user_id FROM Review WHERE review_id = ?");
    $stmt->execute([$review_id]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT role FROM User WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($review && ($review['user_id'] == $user_id || $user['role'] == 'admin')) {
        $stmt = $conn->prepare("DELETE FROM Review WHERE review_id = ?");
        try {
            $stmt->execute([$review_id]);
            echo json_encode(["message" => "Review deleted successfully!"]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Failed to delete review: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["error" => "You can only delete your own reviews or you must be an admin."]);
    }
}

if ($action === 'getReviews') {
    // Get reviews for a specific book
    $book_id = $_GET['book_id'];
    $stmt = $conn->prepare("SELECT Review.*, User.username FROM Review JOIN User ON Review.user_id = User.user_id WHERE book_id = ?");
    $stmt->execute([$book_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($reviews);
}

if ($action === 'filterBooks') {
    // Filter books based on genre, author, and rating
    $genre = $_GET['genre'] ?? '';
    $author = $_GET['author'] ?? '';
    $rating = $_GET['rating'] ?? '';

    $query = "
        SELECT Book.*, Author.fname, Author.lname 
        FROM Book 
        JOIN Author ON Book.author_id = Author.author_id 
        WHERE 1=1
    ";
    $params = [];
    if ($genre) {
        $query .= " AND genre = ?";
        $params[] = $genre;
    }
    if ($author) {
        $query .= " AND CONCAT(Author.fname, ' ', Author.lname) LIKE ?";
        $params[] = '%' . $author . '%';
    }
    if ($rating) {
        $query .= " AND avg_rating >= ?";
        $params[] = $rating;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($books);
}

if ($action === 'getBookDetails') {
    // Get details of a specific book
    $book_id = $_GET['book_id'];
    $stmt = $conn->prepare("SELECT Book.*, Author.fname, Author.lname FROM Book JOIN Author ON Book.author_id = Author.author_id WHERE book_id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT Review.*, User.username FROM Review JOIN User ON Review.user_id = User.user_id WHERE book_id = ?");
    $stmt->execute([$book_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['book' => $book, 'reviews' => $reviews]);
}

if ($action === 'getBooks') {
    // Get all books
    $stmt = $conn->query("SELECT Book.*, Author.fname, Author.lname FROM Book JOIN Author ON Book.author_id = Author.author_id");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($books);
}

if ($action === 'getUserReviews') {
    // Get reviews by a specific user
    $user_id = $_GET['user_id'];
    $stmt = $conn->prepare("SELECT * FROM Review WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($reviews);
}

if ($action === 'addToReadingList') {
    // Add a book to the reading list
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $conn->prepare("SELECT * FROM Reading_List WHERE user_id = ? AND book_id = ? AND list_name = ?");
    $stmt->execute([$data['user_id'], $data['book_id'], $data['list_name']]);
    $existingEntry = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingEntry) {
        echo json_encode(["error" => "Book is already in the reading list."]);
    } else {
        $stmt = $conn->prepare("INSERT INTO Reading_List (user_id, book_id, list_name, date_added, status) VALUES (?, ?, ?, CURRENT_DATE, ?)");
        try {
            $stmt->execute([$data['user_id'], $data['book_id'], $data['list_name'], $data['status']]);
            echo json_encode(["message" => "Book added to reading list successfully!"]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Failed to add book to reading list: " . $e->getMessage()]);
        }
    }
}

if ($action === 'searchBooks') {
    // Search books by title
    $searchTerm = $_GET['searchTerm'] ?? '';
    $stmt = $conn->prepare("
        SELECT Book.*, Author.fname, Author.lname 
        FROM Book 
        JOIN Author ON Book.author_id = Author.author_id 
        WHERE title LIKE ?
    ");
    $stmt->execute(['%' . $searchTerm . '%']);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($books);
}

if ($action === 'getReadingList') {
    // Get the reading list for a specific user
    $user_id = $_GET['user_id'];
    $list_name = $_GET['list_name'] ?? '';
    $sort_by = $_GET['sort_by'] ?? 'status';
    $valid_sort_columns = ['status', 'genre', 'title', 'author'];
    if (!in_array($sort_by, $valid_sort_columns)) {
        $sort_by = 'status';
    }
    $sort_column = $sort_by === 'author' ? 'Author.lname' : $sort_by;
    $stmt = $conn->prepare("
        SELECT Reading_List.list_id, Reading_List.list_name, Reading_List.date_added, Reading_List.status, 
               Book.book_id, Book.title, Book.genre, Book.avg_rating, 
               Author.fname, Author.lname
        FROM Reading_List
        JOIN Book ON Reading_List.book_id = Book.book_id
        JOIN Author ON Book.author_id = Author.author_id
        WHERE Reading_List.user_id = ? AND Reading_List.list_name = ?
        ORDER BY $sort_column
    ");
    $stmt->execute([$user_id, $list_name]);
    $readingList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($readingList);
}

if ($action === 'getUserReadingLists') {
    // Get the reading lists for a specific user
    $user_id = $_GET['user_id'];
    $stmt = $conn->prepare("SELECT DISTINCT list_name FROM Reading_List WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $readingLists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($readingLists);
}

if ($action === 'getRole') {
    $user_id = $_GET['user_id'];
    $stmt = $conn->prepare("SELECT role FROM User WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $role = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($role);
}

if ($action === 'deleteBook') {
    $book_id = $_GET['book_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM Book WHERE book_id = ?");
        $stmt->execute([$book_id]);
        echo json_encode(["message" => "Book deleted successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Failed to delete book: " . $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'createReadingList') {
    $user_id = intval($_POST['user_id']);
    $list_name = trim($_POST['list_name']);
    try {
        $stmt = $conn->prepare("INSERT INTO Reading_List (user_id, list_name, date_added) VALUES (?, ?, CURRENT_DATE)");
        $stmt->execute([$user_id, $list_name]);
        echo json_encode(["message" => "Reading list created successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Failed to create reading list: " . $e->getMessage()]);
    }
    exit;
}

// Delete a reading list
if ($action === 'deleteReadingList') {
    $user_id = $_GET['user_id'];
    $list_name = $_GET['list_name'];
    try {
        $stmt = $conn->prepare("DELETE FROM Reading_List WHERE user_id = ? AND list_name = ?");
        $stmt->execute([$user_id, $list_name]);
        echo json_encode(["message" => "Reading list deleted successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Failed to delete reading list: " . $e->getMessage()]);
    }
    exit;
}

// Add a new book
if ($action === 'addBook') {
    $data = json_decode(file_get_contents('php://input'), true);
    $authorName = $data['author_name'];
    $title = $data['title'];
    $datePublished = $data['date_published'];
    $genre = $data['genre'];

    // Split author name into first name and last name
    $authorNameParts = explode(' ', $authorName);
    $fname = $authorNameParts[0];
    $lname = isset($authorNameParts[1]) ? $authorNameParts[1] : '';

    try {
        // Check if author exists
        $stmt = $conn->prepare("SELECT author_id FROM Author WHERE fname = ? AND lname = ?");
        $stmt->execute([$fname, $lname]);
        $author = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($author) {
            $authorId = $author['author_id'];
        } else {
            // Insert new author
            $stmt = $conn->prepare("INSERT INTO Author (fname, lname) VALUES (?, ?)");
            $stmt->execute([$fname, $lname]);
            $authorId = $conn->lastInsertId();
        }

        // Check if book already exists
        $stmt = $conn->prepare("SELECT * FROM Book WHERE title = ? AND author_id = ?");
        $stmt->execute([$title, $authorId]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($book) {
            echo json_encode(["error" => "Book already exists."]);
        } else {
            $stmt = $conn->prepare("INSERT INTO Book (author_id, title, date_published, avg_rating, genre) VALUES (?, ?, ?, 0, ?)");
            $stmt->execute([$authorId, $title, $datePublished, $genre]);
            echo json_encode(["message" => "Book added successfully!"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Failed to add book: " . $e->getMessage()]);
    }
}

// Remove a book from the reading list
if ($action === 'removeFromReadingList') {
    $list_id = $_GET['list_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM Reading_List WHERE list_id = ?");
        $stmt->execute([$list_id]);
        echo json_encode(["message" => "Book removed from reading list successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Failed to remove book from reading list: " . $e->getMessage()]);
    }
}

// Fallback for invalid actions
if (!$action) {
    echo json_encode(["error" => "Invalid action"]);
    exit;
}
?>
