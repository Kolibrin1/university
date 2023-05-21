CREATE TABLE Authors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50)
);

CREATE TABLE Books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50),
    AuthorID INT,
    Price DECIMAL(10, 2),
    FOREIGN KEY (AuthorID) REFERENCES Authors(id)
);

CREATE TABLE Sellers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50)
);

CREATE TABLE Buyers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50)
);

CREATE TABLE Sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    BookID INT,
    SellerID INT,
    BuyerID INT,
    SaleDate DATE,
    FOREIGN KEY (BookID) REFERENCES Books(id),
    FOREIGN KEY (SellerID) REFERENCES Sellers(id),
    FOREIGN KEY (BuyerID) REFERENCES Buyers(id)
);