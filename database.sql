-- Drop existing tables if they exist
DROP TABLE IF EXISTS Review;
DROP TABLE IF EXISTS Reading_List;
DROP TABLE IF EXISTS Book;
DROP TABLE IF EXISTS Author;
DROP TABLE IF EXISTS User;

-- Drop existing triggers if they exist
DROP TRIGGER IF EXISTS update_avg_rating;
DROP TRIGGER IF EXISTS update_avg_rating_on_delete;
DROP TRIGGER IF EXISTS update_avg_rating_on_update;

-- Create User table
CREATE TABLE User (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,  -- Added password column
    date_joined DATE NOT NULL,
    role ENUM('admin', 'user') NOT NULL
);

-- Create Author table
CREATE TABLE Author (
    author_id INT AUTO_INCREMENT PRIMARY KEY,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL
);

-- Create Book table
CREATE TABLE Book (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT,
    title VARCHAR(255) NOT NULL,
    date_published DATE NOT NULL,
    avg_rating DECIMAL(3, 2),
    genre VARCHAR(50),
    FOREIGN KEY (author_id) REFERENCES Author(author_id) ON DELETE SET NULL ON UPDATE RESTRICT
);

-- Create Reading List table
CREATE TABLE Reading_List (
    list_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    book_id INT,
    list_name VARCHAR(100),
    date_added DATE NOT NULL,
    status ENUM('to read', 'reading', 'read', 'dropped') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE ON UPDATE RESTRICT,
    FOREIGN KEY (book_id) REFERENCES Book(book_id) ON DELETE CASCADE ON UPDATE RESTRICT
);

-- Create Review table
CREATE TABLE Review (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    book_id INT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE ON UPDATE RESTRICT,
    FOREIGN KEY (book_id) REFERENCES Book(book_id) ON DELETE CASCADE ON UPDATE RESTRICT
);

-- Triggers to update avg_rating in Book table
DELIMITER //

CREATE TRIGGER update_avg_rating AFTER INSERT ON Review
FOR EACH ROW
BEGIN
    UPDATE Book
    SET avg_rating = (SELECT COALESCE(AVG(rating), 0) FROM Review WHERE book_id = NEW.book_id)
    WHERE book_id = NEW.book_id;
END;
//

CREATE TRIGGER update_avg_rating_on_delete AFTER DELETE ON Review
FOR EACH ROW
BEGIN
    UPDATE Book
    SET avg_rating = (SELECT COALESCE(AVG(rating), 0) FROM Review WHERE book_id = OLD.book_id)
    WHERE book_id = OLD.book_id;
END;
//

CREATE TRIGGER update_avg_rating_on_update AFTER UPDATE ON Review
FOR EACH ROW
BEGIN
    UPDATE Book
    SET avg_rating = (SELECT COALESCE(AVG(rating), 0) FROM Review WHERE book_id = NEW.book_id)
    WHERE book_id = NEW.book_id;
END;
//

DELIMITER ;

-- Sample data for User table
INSERT INTO User (username, password, date_joined, role) VALUES
('john_doe', 'password1', '2023-01-01', 'user'),
('admin_user', 'password2', '2023-01-02', 'admin'),
('jane_smith', 'password3', '2023-01-03', 'user'),
('emily_bronte', 'password4', '2023-01-04', 'user'),
('mark_twain', 'password5', '2023-01-05', 'user'),
('charles_dickens', 'password6', '2023-01-06', 'user'),
('leo_tolstoy', 'password7', '2023-01-07', 'user'),
('george_orwell', 'password8', '2023-01-08', 'user'),
('jk_rowling', 'password9', '2023-01-09', 'user'),
('agatha_christie', 'password10', '2023-01-10', 'user');

-- Sample data for Author table
INSERT INTO Author (fname, lname) VALUES
('John', 'Grisham'),
('Jane', 'Austen'),
('Emily', 'Dickinson'),
('Mark', 'Twain'),
('Charles', 'Dickens'),
('Leo', 'Tolstoy'),
('George', 'Orwell'),
('J.K.', 'Rowling'),
('Agatha', 'Christie'),
('Ernest', 'Hemingway'),
('Herman', 'Melville'),
('Homer', ''),
('Fyodor', 'Dostoevsky'),
('Aldous', 'Huxley'),
('Ray', 'Bradbury'),
('J.R.R.', 'Tolkien'),
('C.S.', 'Lewis'),
('Gabriel', 'Garcia Marquez'),
('Albert', 'Camus'),
('Cormac', 'McCarthy'),
('Ralph', 'Ellison'),
('Richard', 'Wright');

-- Sample data for Book table
INSERT INTO Book (author_id, title, date_published, avg_rating, genre) VALUES
(1, 'The Firm', '1991-02-01', 0, 'Legal Thriller'),
(1, 'The Pelican Brief', '1992-01-01', 0, 'Legal Thriller'),
(2, 'Pride and Prejudice', '1813-01-28', 0, 'Romance'),
(2, 'Sense and Sensibility', '1811-01-01', 0, 'Romance'),
(3, 'Collected Poems', '1890-01-01', 0, 'Poetry'),
(4, 'Adventures of Huckleberry Finn', '1884-12-10', 0, 'Adventure'),
(4, 'The Adventures of Tom Sawyer', '1876-01-01', 0, 'Adventure'),
(5, 'A Tale of Two Cities', '1859-04-30', 0, 'Historical Fiction'),
(5, 'Great Expectations', '1861-01-01', 0, 'Literary Fiction'),
(6, 'War and Peace', '1869-01-01', 0, 'Historical Fiction'),
(6, 'Anna Karenina', '1877-01-01', 0, 'Literary Fiction'),
(7, '1984', '1949-06-08', 0, 'Dystopian'),
(7, 'Animal Farm', '1945-08-17', 0, 'Political Satire'),
(8, 'Harry Potter and the Sorcerers Stone', '1997-06-26', 0, 'Fantasy'),
(8, 'Harry Potter and the Chamber of Secrets', '1998-07-02', 0, 'Fantasy'),
(9, 'Murder on the Orient Express', '1934-01-01', 0, 'Mystery'),
(9, 'The ABC Murders', '1936-01-06', 0, 'Mystery'),
(10, 'The Old Man and the Sea', '1952-09-01', 0, 'Literary Fiction'),
(10, 'For Whom the Bell Tolls', '1940-10-21', 0, 'War Fiction'),
(11, 'Moby Dick', '1851-10-18', 0, 'Adventure'),
(11, 'Billy Budd', '1924-01-01', 0, 'Adventure'),
(12, 'The Odyssey', '800-01-01', 0, 'Epic'),
(12, 'The Iliad', '750-01-01', 0, 'Epic'),
(13, 'Crime and Punishment', '1866-01-01', 0, 'Psychological Fiction'),
(13, 'The Brothers Karamazov', '1880-01-01', 0, 'Philosophical Fiction'),
(14, 'Brave New World', '1932-01-01', 0, 'Dystopian'),
(14, 'Island', '1962-01-01', 0, 'Utopian'),
(15, 'Fahrenheit 451', '1953-10-19', 0, 'Dystopian'),
(15, 'The Martian Chronicles', '1950-01-01', 0, 'Science Fiction'),
(16, 'The Hobbit', '1937-09-21', 0, 'Fantasy'),
(16, 'The Lord of the Rings', '1954-07-29', 0, 'Fantasy'),
(16, 'The Silmarillion', '1977-09-15', 0, 'Fantasy'),
(17, 'The Chronicles of Narnia', '1950-10-16', 0, 'Fantasy'),
(17, 'The Screwtape Letters', '1942-01-01', 0, 'Christian Fiction'),
(18, 'One Hundred Years of Solitude', '1967-05-30', 0, 'Magic Realism'),
(18, 'Love in the Time of Cholera', '1985-01-01', 0, 'Magic Realism'),
(19, 'The Stranger', '1942-01-01', 0, 'Philosophical Fiction'),
(19, 'The Plague', '1947-01-01', 0, 'Philosophical Fiction'),
(20, 'The Road', '2006-09-26', 0, 'Post-Apocalyptic'),
(20, 'Blood Meridian', '1985-04-28', 0, 'Western'),
(21, 'Invisible Man', '1952-04-14', 0, 'Literary Fiction'),
(22, 'Native Son', '1940-03-01', 0, 'Social Protest Fiction');

-- Sample data for Reading List table
INSERT INTO Reading_List (user_id, book_id, list_name, date_added, status) VALUES
(1, 1, 'Favorites', '2023-01-01', 'read'),
(1, 2, 'To Read', '2023-01-02', 'to read'),
(2, 3, 'Favorites', '2023-01-03', 'reading'),
(2, 4, 'To Read', '2023-01-04', 'to read'),
(3, 5, 'Favorites', '2023-01-05', 'read'),
(3, 6, 'To Read', '2023-01-06', 'to read'),
(4, 7, 'Favorites', '2023-01-07', 'reading'),
(4, 8, 'To Read', '2023-01-08', 'to read'),
(5, 9, 'Favorites', '2023-01-09', 'read'),
(5, 10, 'To Read', '2023-01-10', 'to read'),
(6, 11, 'Favorites', '2023-01-11', 'read'),
(6, 12, 'To Read', '2023-01-12', 'to read'),
(7, 13, 'Favorites', '2023-01-13', 'reading'),
(7, 14, 'To Read', '2023-01-14', 'to read'),
(8, 15, 'Favorites', '2023-01-15', 'read'),
(8, 16, 'To Read', '2023-01-16', 'to read'),
(9, 17, 'Favorites', '2023-01-17', 'reading'),
(9, 18, 'To Read', '2023-01-18', 'to read'),
(10, 19, 'Favorites', '2023-01-19', 'read'),
(10, 20, 'To Read', '2023-01-20', 'to read'),
(1, 21, 'Favorites', '2023-01-21', 'reading'),
(1, 22, 'To Read', '2023-01-22', 'to read'),
(2, 23, 'Favorites', '2023-01-23', 'read'),
(2, 24, 'To Read', '2023-01-24', 'to read'),
(3, 25, 'Favorites', '2023-01-25', 'reading'),
(3, 26, 'To Read', '2023-01-26', 'to read'),
(4, 27, 'Favorites', '2023-01-27', 'read'),
(4, 28, 'To Read', '2023-01-28', 'to read'),
(5, 29, 'Favorites', '2023-01-29', 'reading'),
(5, 30, 'To Read', '2023-01-30', 'to read'),
(6, 31, 'Favorites', '2023-01-31', 'read'),
(6, 32, 'To Read', '2023-02-01', 'to read'),
(7, 33, 'Favorites', '2023-02-02', 'reading'),
(7, 34, 'To Read', '2023-02-03', 'to read'),
(8, 35, 'Favorites', '2023-02-04', 'read'),
(8, 36, 'To Read', '2023-02-05', 'to read'),
(9, 37, 'Favorites', '2023-02-06', 'reading'),
(9, 38, 'To Read', '2023-02-07', 'to read'),
(10, 39, 'Favorites', '2023-02-08', 'read'),
(10, 40, 'To Read', '2023-02-09', 'to read'),
(1, 41, 'Favorites', '2023-02-10', 'reading'),
(1, 42, 'To Read', '2023-02-11', 'to read');

-- Sample data for Review table
INSERT INTO Review (user_id, book_id, rating, review_text) VALUES
(1, 1, 5, 'A thrilling read from start to finish.'),
(1, 2, 4, 'A classic romance with timeless appeal.'),
(2, 3, 4, 'Beautiful and thought-provoking poetry.'),
(2, 4, 3, 'An adventurous tale, but a bit dated.'),
(3, 5, 5, 'A masterpiece of historical fiction.'),
(3, 6, 4, 'A lengthy read, but worth it.'),
(4, 7, 5, 'A chilling dystopian novel.'),
(4, 8, 5, 'Magical and captivating.'),
(5, 9, 4, 'A clever and engaging mystery.'),
(5, 10, 3, 'A simple yet profound story.'),
(6, 11, 5, 'A compelling story with deep characters.'),
(6, 12, 4, 'A powerful and moving novel.'),
(7, 13, 5, 'A timeless classic.'),
(7, 14, 3, 'A bit long, but worth the read.'),
(8, 15, 4, 'An epic tale of adventure.'),
(8, 16, 4, 'A foundational work of literature.'),
(9, 17, 5, 'A profound psychological exploration.'),
(9, 18, 5, 'A philosophical masterpiece.'),
(10, 19, 4, 'A chilling dystopian vision.'),
(10, 20, 5, 'A thought-provoking novel.'),
(1, 21, 5, 'A magical journey.'),
(1, 22, 5, 'An epic fantasy adventure.'),
(2, 3, 4, 'A rich and complex mythology.'),
(2, 4, 4, 'A delightful childrens series.'),
(3, 5, 5, 'A brilliant political satire.'),
(3, 6, 4, 'A powerful social commentary.'),
(4, 7, 4, 'A moving and tragic story.'),
(4, 8, 5, 'A sweeping family saga.'),
(5, 9, 4, 'A dark and thought-provoking novel.'),
(5, 10, 4, 'A classic horror story.'),
(6, 11, 5, 'A groundbreaking work of science fiction.'),
(6, 12, 4, 'A surreal and disturbing tale.'),
(7, 13, 4, 'A nightmarish vision of bureaucracy.'),
(7, 14, 4, 'A haunting and unfinished novel.'),
(8, 15, 5, 'A magical and enchanting story.'),
(8, 16, 4, 'A beautiful and tragic love story.'),
(9, 17, 5, 'A profound existential novel.'),
(9, 18, 4, 'A powerful allegory of human suffering.'),
(10, 19, 4, 'A dark and introspective novel.'),
(10, 20, 5, 'A harrowing post-apocalyptic tale.'),
(1, 21, 4, 'A brutal and poetic western.'),
(1, 22, 4, 'A gripping and violent thriller.'),
(2, 23, 5, 'A beautifully written novel.'),
(2, 24, 4, 'A powerful and moving war story.'),
(3, 25, 4, 'A tragic love story set in wartime.'),
(3, 26, 5, 'A complex and challenging novel.'),
(4, 27, 4, 'A dark and tragic family saga.'),
(4, 28, 4, 'A haunting and powerful novel.'),
(5, 29, 5, 'A profound exploration of identity.'),
(5, 30, 4, 'A powerful social protest novel.'),
(1, 1, 5, 'Another thrilling read.'),
(2, 2, 4, 'Another classic romance.'),
(3, 3, 4, 'More beautiful poetry.'),
(4, 4, 3, 'Another adventurous tale.'),
(5, 5, 5, 'Another historical masterpiece.'),
(6, 6, 4, 'Another lengthy read.'),
(7, 7, 5, 'Another chilling dystopian novel.'),
(8, 8, 5, 'More magical and captivating.'),
(9, 9, 4, 'Another clever mystery.'),
(10, 10, 3, 'Another simple story.'),
(1, 11, 5, 'Another compelling story.'),
(2, 12, 4, 'Another powerful novel.'),
(3, 13, 5, 'Another timeless classic.'),
(4, 14, 3, 'Another lengthy read.'),
(5, 15, 4, 'Another epic tale.'),
(6, 16, 4, 'Another foundational work.'),
(7, 17, 5, 'Another psychological exploration.'),
(8, 18, 5, 'Another philosophical masterpiece.'),
(9, 19, 4, 'Another dystopian vision.'),
(10, 20, 5, 'Another thought-provoking novel.'),
(1, 21, 5, 'Another magical journey.'),
(2, 22, 5, 'Another epic fantasy.'),
(3, 23, 4, 'Another rich mythology.'),
(4, 24, 4, 'Another delightful series.'),
(5, 25, 5, 'Another political satire.'),
(6, 26, 4, 'Another social commentary.'),
(7, 27, 4, 'Another tragic story.'),
(8, 28, 5, 'Another family saga.'),
(9, 29, 4, 'Another thought-provoking novel.'),
(10, 30, 4, 'Another horror story.'),
(1, 31, 5, 'Another groundbreaking work.'),
(2, 32, 4, 'Another disturbing tale.'),
(3, 33, 4, 'Another vision of bureaucracy.'),
(4, 34, 4, 'Another unfinished novel.'),
(5, 35, 5, 'Another enchanting story.'),
(6, 36, 4, 'Another tragic love story.'),
(7, 37, 5, 'Another existential novel.'),
(8, 38, 4, 'Another allegory of suffering.'),
(9, 39, 4, 'Another introspective novel.'),
(10, 40, 5, 'Another post-apocalyptic tale.'),
(1, 41, 4, 'Another poetic western.'),
(2, 42, 4, 'Another violent thriller.'),
(3, 1, 5, 'Yet another thrilling read.'),
(4, 2, 4, 'Yet another classic romance.'),
(5, 3, 4, 'Yet more beautiful poetry.'),
(6, 4, 3, 'Yet another adventurous tale.'),
(7, 5, 5, 'Yet another historical masterpiece.'),
(8, 6, 4, 'Yet another lengthy read.'),
(9, 7, 5, 'Yet another chilling dystopian novel.'),
(10, 8, 5, 'Yet more magical and captivating.'),
(1, 9, 4, 'Yet another clever mystery.'),
(2, 10, 3, 'Yet another simple story.'),
(3, 11, 5, 'Yet another compelling story.'),
(4, 12, 4, 'Yet another powerful novel.'),
(5, 13, 5, 'Yet another timeless classic.'),
(6, 14, 3, 'Yet another lengthy read.'),
(7, 15, 4, 'Yet another epic tale.'),
(8, 16, 4, 'Yet another foundational work.'),
(9, 17, 5, 'Yet another psychological exploration.'),
(10, 18, 5, 'Yet another philosophical masterpiece.'),
(1, 19, 4, 'Yet another dystopian vision.'),
(2, 20, 5, 'Yet another thought-provoking novel.'),
(3, 21, 5, 'Yet another magical journey.'),
(4, 22, 5, 'Yet another epic fantasy.'),
(5, 23, 4, 'Yet another rich mythology.'),
(6, 24, 4, 'Yet another delightful series.'),
(7, 25, 5, 'Yet another political satire.'),
(8, 26, 4, 'Yet another social commentary.'),
(9, 27, 4, 'Yet another tragic story.'),
(10, 28, 5, 'Yet another family saga.'),
(1, 29, 4, 'Yet another thought-provoking novel.'),
(2, 30, 4, 'Yet another horror story.'),
(3, 31, 5, 'Yet another groundbreaking work.'),
(4, 32, 4, 'Yet another disturbing tale.'),
(5, 33, 4, 'Yet another vision of bureaucracy.'),
(6, 34, 4, 'Yet another unfinished novel.'),
(7, 35, 5, 'Yet another enchanting story.'),
(8, 36, 4, 'Yet another tragic love story.'),
(9, 37, 5, 'Yet another existential novel.'),
(10, 38, 4, 'Yet another allegory of suffering.'),
(1, 39, 4, 'Yet another introspective novel.'),
(2, 40, 5, 'Yet another post-apocalyptic tale.'),
(3, 41, 4, 'Yet another poetic western.'),
(4, 42, 4, 'Yet another violent thriller.');



