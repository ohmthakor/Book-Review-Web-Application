// Event listener for searching books
document.getElementById("search-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    const searchTerm = document.getElementById("search-input").value;
    try {
        const admin = await isUserAdmin();
        const response = await fetch(`server.php?action=searchBooks&searchTerm=${searchTerm}`);
        const books = await response.json();
        displayBooks(books, admin);
    } catch (err) {
        console.error("Error searching books:", err);
    }
});

// Event listener for filtering books
document.getElementById("filter-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    const genre = document.getElementById("filter-genre").value;
    const author = document.getElementById("filter-author").value;
    const rating = document.getElementById("filter-rating").value;
    try {
        const admin = await isUserAdmin();
        const response = await fetch(`server.php?action=filterBooks&genre=${genre}&author=${author}&rating=${rating}`);
        const books = await response.json();
        displayBooks(books, admin);
    } catch (err) {
        console.error("Error filtering books:", err);
    }
});

async function isUserAdmin() {
    const userId = document.getElementById("user_id").value;
    try {
        const response = await fetch(`server.php?action=getRole&user_id=${userId}`);
        if (response.ok) {
            const data = await response.json();
            if (data[0].role === 'admin') {
                return true;
            }
        }
    } catch (error) {
        console.error("Error:", error);
    }
    return false;
}

// Function to display books -- NOT THE MAIN DISPLAY
function displayBooks(books, admin) {
    const bookList = document.getElementById("book-list");
    bookList.innerHTML = "";
    books.forEach((book) => {
        const li = document.createElement("li");
        li.className = "book-item";
        li.innerHTML = `
            <strong>${book.title}</strong> by ${book.fname} ${book.lname} <br>
            Genre: ${book.genre} <br>
            Rating: ${book.avg_rating}
            <button onclick="viewBookDetails(${book.book_id})">View Details</button>
            <button onclick="addToReadingList(${book.book_id})">Add to Reading List</button>
            <button onclick="showReviewPopup(${book.book_id})">Add Review</button>
            ${admin ? `<button onclick="deleteBook(${book.book_id})">Delete</button>` : ''}
        `;
        bookList.appendChild(li);
    });
}

// Function to show review popup
function showReviewPopup(bookId) {
    const popup = document.createElement('div');
    popup.className = 'custom-popup';
    popup.innerHTML = `
        <div class="popup-content">
            <h3>Add Review</h3>
            <form id="popup-review-form">
                <input type="hidden" name="user_id" value="${document.getElementById('user_id').value}">
                <input type="hidden" id="popup-book-id" name="book_id" value="${bookId}">
                <label for="popup-rating">Rating (1-5):</label>
                <input type="number" id="popup-rating" name="rating" min="1" max="5" required>
                <label for="popup-review-text">Review:</label>
                <textarea id="popup-review-text" name="review_text" required></textarea>
                <button type="submit">Add Review</button>
                <button type="button" onclick="closePopup()">Cancel</button>
            </form>
        </div>
    `;
    document.body.appendChild(popup);

    document.getElementById("popup-review-form").addEventListener("submit", async (e) => {
        e.preventDefault();
    
        const formData = new FormData(e.target);
        const data = {
            user_id: formData.get("user_id"),
            book_id: formData.get("book_id"),
            rating: formData.get("rating"),
            review_text: formData.get("review_text"),
        };
    
        try {
            const response = await fetch("server.php?action=addReview", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data),
            });
            const result = await response.json();
            if (result.message) {
                alert(result.message);
                fetchReviews(data.book_id);
                popup.remove();
            } else {
                alert(result.error);
            }
        } catch (err) {
            console.error("Error:", err);
        }
    });
}

// Function to close popup
function closePopup() {
    const popup = document.querySelector('.custom-popup');
    if (popup) {
        document.body.removeChild(popup);
    }
}

// Function to fetch reviews for a book
async function fetchReviews(bookId) {
    try {
        const response = await fetch(`server.php?action=getReviews&book_id=${bookId}`);
        const reviews = await response.json();
        const reviewsList = document.getElementById("reviews-list");
        if (reviewsList) {
            reviewsList.innerHTML = "";
            reviews.forEach((review) => {
                const li = document.createElement("li");
                li.innerHTML = `
                    User: ${review.username}, Rating: ${review.rating} <br>
                    Review: ${review.review_text}
                    ${review.user_id == userId || userRole == 'admin' ? `
                    <button onclick="deleteReview(${review.review_id})">Delete</button>
                    <button onclick="editReviewPrompt(${review.review_id}, ${review.rating}, '${review.review_text}')">Edit</button>
                    ` : ''}
                `;
                reviewsList.appendChild(li);
            });
        }
    } catch (err) {
        console.error("Error fetching reviews:", err);
    }
}

// Function to delete a review
async function deleteReview(reviewId) {
    const userId = document.getElementById("user_id").value;
    const bookId = document.querySelector("#book-details h2").dataset.bookId; // Get the bookId from the book details
    const response = await fetch(`server.php?action=deleteReview&review_id=${reviewId}&user_id=${userId}`, { method: "GET" });
    const result = await response.json();
    if (result.message) {
        alert(result.message);
        fetchReviews(bookId); // Pass the correct bookId
    } else {
        alert(result.error);
    }
}

// Function to prompt for editing a review
function editReviewPrompt(reviewId, currentRating, currentText) {
    const popup = document.createElement('div');
    popup.className = 'custom-popup';
    popup.innerHTML = `
        <div class="popup-content">
            <h3>Edit Review</h3>
            <label for="edit-rating">Rating (1-5):</label>
            <input type="number" id="edit-rating" value="${currentRating}" min="1" max="5" required>
            <label for="edit-review-text">Review:</label>
            <textarea id="edit-review-text" required>${currentText}</textarea>
            <button onclick="editReview(${reviewId}, document.getElementById('edit-rating').value, document.getElementById('edit-review-text').value)">Save</button>
            <button type="button" onclick="closePopup()">Cancel</button>
        </div>
    `;
    document.body.appendChild(popup);
}

// Function to edit a review
async function editReview(reviewId, newRating, newText) {
    const userId = document.getElementById("user_id").value;
    const bookId = document.querySelector("#book-details h2").dataset.bookId; // Get the bookId from the book details
    const data = { review_id: reviewId, rating: newRating, review_text: newText, user_id: userId };
    const response = await fetch("server.php?action=editReview", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
    });
    const result = await response.json();
    if (result.message) {
        alert(result.message);
        fetchReviews(bookId); // Pass the correct bookId
        closePopup();
    } else {
        alert(result.error);
    }
}

async function deleteBook(bookId) {
    try {
        const response = await fetch(`server.php?action=deleteBook&book_id=${bookId}`, { method: "GET" });
        const result = await response.json();
        if (result.message) {
            alert(result.message);
            fetchBooks();
        } else {
            alert(result.error);
        }
    } catch (error) {
        console.error("Error deleting book:", error);
    }
}

// Function to fetch all books
async function fetchBooks() {
    try {
        const admin = await isUserAdmin();
        const response = await fetch("server.php?action=getBooks");
        const books = await response.json();
        displayBooks(books, admin);
    } catch (err) {
        console.error("Error fetching books:", err);
    }
}

// Function to view book details
async function viewBookDetails(bookId) {
    try {
        const response = await fetch(`server.php?action=getBookDetails&book_id=${bookId}`);
        const result = await response.json();
        const bookDetails = document.getElementById("book-details");
        bookDetails.innerHTML = `
            <h2 data-book-id="${bookId}">${result.book.title}</h2>
            <p>Author: ${result.book.fname} ${result.book.lname}</p>
            <p>Genre: ${result.book.genre}</p>
            <p>Rating: ${result.book.avg_rating}</p>
            <h3>Reviews:</h3>
            <ul id="reviews-list"></ul>
        `;
        const reviewsList = document.getElementById("reviews-list");
        const userId = document.getElementById("user_id").value;
        const userRole = await isUserAdmin() ? 'admin' : 'user';
        result.reviews.forEach((review) => {
            const li = document.createElement("li");
            li.innerHTML = `
                User: ${review.username}, Rating: ${review.rating} <br>
                Review: ${review.review_text}
                ${(review.user_id == userId || userRole == 'admin') ? `
                <button onclick="deleteReview(${review.review_id})">Delete</button>
                <button onclick="editReviewPrompt(${review.review_id}, ${review.rating}, '${review.review_text}')">Edit</button>
                ` : ''}
            `;
            reviewsList.appendChild(li);
        });
    } catch (err) {
        console.error("Error fetching book details:", err);
    }
}

// Function to fetch user reviews
async function fetchUserReviews(userId) {
    try {
        const response = await fetch(`server.php?action=getUserReviews&user_id=${userId}`);
        const reviews = await response.json();
        const userReviewsList = document.getElementById("user-reviews-list");
        if (userReviewsList) {
            userReviewsList.innerHTML = "";
            reviews.forEach((review) => {
                const li = document.createElement("li");
                li.innerHTML = `
                    Book ID: ${review.book_id}, Rating: ${review.rating} <br>
                    Review: ${review.review_text}
                    <button onclick="deleteReview(${review.review_id})">Delete</button>
                    <button onclick="editReviewPrompt(${review.review_id}, ${review.rating}, '${review.review_text}')">Edit</button>
                `;
                userReviewsList.appendChild(li);
            });
        }
    } catch (err) {
        console.error("Error fetching user reviews:", err);
    }
}

// Function to add a book to the reading list
async function addToReadingList(bookId) {
    const userId = document.getElementById("user_id").value;

    // Fetch user's reading lists
    try {
        const responseLists = await fetch(`server.php?action=getUserReadingLists&user_id=${userId}`);
        const readingLists = await responseLists.json();

        // Create dropdown for reading lists
        let listOptions = '';
        readingLists.forEach(list => {
            listOptions += `<option value="${list.list_name}">${list.list_name}</option>`;
        });

        // Create dropdown for status
        const statusOptions = `
            <option value="to read">To Read</option>
            <option value="reading">Reading</option>
            <option value="read">Read</option>
            <option value="dropped">Dropped</option>
        `;

        // Create custom popup
        const popup = document.createElement('div');
        popup.className = 'custom-popup';
        popup.innerHTML = `
            <div class="popup-content">
                <h3>Add to Reading List</h3>
                <label for="list-select">Select the reading list:</label>
                <select id="list-select">${listOptions}</select>
                <label for="status-select">Select the status:</label>
                <select id="status-select">${statusOptions}</select>
                <button id="add-to-list-button">Add</button>
                <button id="cancel-button">Cancel</button>
            </div>
        `;
        document.body.appendChild(popup);

        // Add event listeners for buttons
        document.getElementById('add-to-list-button').addEventListener('click', async () => {
            const listName = document.getElementById('list-select').value;
            const status = document.getElementById('status-select').value;

            if (listName && status) {
                const data = { user_id: userId, book_id: bookId, list_name: listName, status: status };
                try {
                    const response = await fetch("server.php?action=addToReadingList", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(data),
                    });
                    const result = await response.json();
                    if (result.message) {
                        alert(result.message);
                        fetchReadingList(userId); // Update the reading list
                    } else {
                        alert(result.error);
                    }
                } catch (err) {
                    console.error("Error adding book to reading list:", err);
                }
            }
            document.body.removeChild(popup);
        });

        document.getElementById('cancel-button').addEventListener('click', () => {
            document.body.removeChild(popup);
        });
    } catch (err) {
        console.error("Error fetching reading lists:", err);
    }
}

// Function to fetch the reading list
async function fetchReadingList(userId) {
    const listNameElement = document.getElementById("reading-list-select");
    if (!listNameElement) {
        console.error("Element with id 'reading-list-select' not found.");
        return;
    }
    const listName = listNameElement.value;
    if (!listName) return;
    try {
        const response = await fetch(`server.php?action=getReadingList&user_id=${userId}&list_name=${listName}`);
        const readingList = await response.json();
        const readingListDetails = document.getElementById("reading-list-details");
        if (readingListDetails) {
            readingListDetails.innerHTML = "";

            readingList.forEach((item) => {
                const div = document.createElement("div");
                div.className = "reading-list-box";
                div.innerHTML = `
                    <p>Book: ${item.title}</p>
                    <p>Author: ${item.fname} ${item.lname}</p>
                    <p>Genre: ${item.genre}</p>
                    <p>Status: ${item.status}</p>
                    <p>Date Added: ${item.date_added}</p>
                    <button onclick="removeFromReadingList(${item.list_id})">Remove</button>
                `;
                readingListDetails.appendChild(div);
            });
        }
    } catch (err) {
        console.error("Error fetching reading list:", err);
    }
}

// Load reviews on page load
fetchReviews();

// Load books on page load
fetchBooks();

// Load user reviews on page load
fetchUserReviews(document.getElementById("user_id").value);

// Load reading list on page load
fetchReadingList(document.getElementById("user_id").value);

// Function to add a book
async function addBook(authorName, title, datePublished, genre) {
    const data = { author_name: authorName, title: title, date_published: datePublished, genre: genre };
    try {
        const response = await fetch("server.php?action=addBook", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data),
        });
        const result = await response.json();
        if (result.message) {
            alert(result.message);
            fetchBooks();
        } else {
            alert(result.error);
        }
    } catch (err) {
        console.error("Error adding book:", err);
        alert("Error adding book: " + err.message);
    }
}

function showAddBookPopup() {
    const popup = document.createElement('div');
    popup.className = 'custom-popup';
    popup.innerHTML = `
        <div class="popup-content">
            <h3>Add New Book</h3>
            <form id="add-book-form">
                <label for="author-name">Author Name:</label>
                <input type="text" id="author-name" name="author_name" required>
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
                <label for="date-published">Date Published:</label>
                <input type="date" id="date-published" name="date_published" required>
                <label for="genre">Genre:</label>
                <input type="text" id="genre" name="genre" required>
                <button type="submit">Add Book</button>
                <button type="button" onclick="closePopup()">Cancel</button>
            </form>
        </div>
    `;
    document.body.appendChild(popup);

    document.getElementById('add-book-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const authorName = document.getElementById('author-name').value;
        const title = document.getElementById('title').value;
        const datePublished = document.getElementById('date-published').value;
        const genre = document.getElementById('genre').value;
        await addBook(authorName, title, datePublished, genre);
        closePopup();
    });
}

function closePopup() {
    const popup = document.querySelector('.custom-popup');
    if (popup) {
        document.body.removeChild(popup);
    }
}

// Function to delete a book
async function deleteBook(bookId) {
    try {
        const response = await fetch(`server.php?action=deleteBook&book_id=${bookId}`, { method: "GET" });
        const result = await response.json();
        if (result.message) {
            alert(result.message);
            fetchBooks();
        } else {
            alert(result.error);
        }
    } catch (error) {
        console.error("Error deleting book:", error);
    }
}

// Function to remove a book from the reading list
async function removeFromReadingList(listId) {
    try {
        const response = await fetch(`server.php?action=removeFromReadingList&list_id=${listId}`);
        const result = await response.json();
        if (result.message) {
            alert(result.message);
            fetchReadingList(document.getElementById("user_id").value);
        } else {
            alert(result.error);
        }
    } catch (err) {
        console.error("Error removing book from reading list:", err);
    }
}

function showAddBookPopup() {
    const popup = document.createElement('div');
    popup.className = 'custom-popup';
    popup.innerHTML = `
        <div class="popup-content">
            <h3>Add New Book</h3>
            <form id="add-book-form">
                <label for="author-name">Author Name:</label>
                <input type="text" id="author-name" name="author_name" required>
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
                <label for="date-published">Date Published:</label>
                <input type="date" id="date-published" name="date_published" required>
                <label for="genre">Genre:</label>
                <input type="text" id="genre" name="genre" required>
                <button type="submit">Add Book</button>
                <button type="button" onclick="closePopup()">Cancel</button>
            </form>
        </div>
    `;
    document.body.appendChild(popup);

    document.getElementById('add-book-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const authorName = document.getElementById('author-name').value;
        const title = document.getElementById('title').value;
        const datePublished = document.getElementById('date-published').value;
        const genre = document.getElementById('genre').value;
        await addBook(authorName, title, datePublished, genre);
        closePopup();
    });
}

function closePopup() {
    const popup = document.querySelector('.custom-popup');
    if (popup) {
        document.body.removeChild(popup);
    }
}
