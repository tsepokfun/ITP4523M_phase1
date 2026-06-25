DROP DATABASE IF EXISTS projectDB;
CREATE DATABASE projectDB CHARACTER SET utf8;
USE projectDB;

-- ============================================
-- TABLE 1: Customer
-- ============================================
DROP TABLE IF EXISTS Customer;
CREATE TABLE Customer (
    customer_id     INT AUTO_INCREMENT,
    customer_name   VARCHAR(100) NOT NULL,
    password        VARCHAR(255) NOT NULL,
    contact_number  VARCHAR(20),
    address         VARCHAR(255),
    PRIMARY KEY (customer_id)
) ENGINE = InnoDB;

-- ============================================
-- TABLE 2: Staff
-- ============================================
DROP TABLE IF EXISTS Staff;
CREATE TABLE Staff (
    staff_id    INT AUTO_INCREMENT,
    staff_name  VARCHAR(100) NOT NULL,
    password    VARCHAR(255) NOT NULL,
    PRIMARY KEY (staff_id)
) ENGINE = InnoDB;

-- ============================================
-- TABLE 3: Material
-- ============================================
DROP TABLE IF EXISTS Material;
CREATE TABLE Material (
    material_id       INT AUTO_INCREMENT,
    material_name     VARCHAR(100) NOT NULL,
    physical_quantity INT NOT NULL DEFAULT 0,
    unit              VARCHAR(50) NOT NULL,
    PRIMARY KEY (material_id)
) ENGINE = InnoDB;

-- ============================================
-- TABLE 4: Furniture
-- ============================================
DROP TABLE IF EXISTS Furniture;
CREATE TABLE Furniture (
    furniture_id    INT AUTO_INCREMENT,
    furniture_name  VARCHAR(100) NOT NULL,
    description     TEXT,
    image           LONGBLOB,
    price           DECIMAL(10,2) NOT NULL,
    stock_quantity  INT NOT NULL DEFAULT 0,
    PRIMARY KEY (furniture_id)
) ENGINE = InnoDB;

-- ============================================
-- TABLE 5: Furniture_Material (Junction Table)
-- A furniture can use one or multiple materials
-- ============================================
DROP TABLE IF EXISTS Furniture_Material;
CREATE TABLE Furniture_Material (
    furniture_id        INT NOT NULL,
    material_id         INT NOT NULL,
    material_quantity   DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (furniture_id, material_id),
    FOREIGN KEY (furniture_id) REFERENCES Furniture(furniture_id),
    FOREIGN KEY (material_id)  REFERENCES Material(material_id)
) ENGINE = InnoDB;

-- ============================================
-- TABLE 6: Orders
-- ============================================
DROP TABLE IF EXISTS Orders;
CREATE TABLE Orders (
    order_id         INT AUTO_INCREMENT,
    customer_id      INT NOT NULL,
    furniture_id     INT NOT NULL,
    order_date       DATE NOT NULL,
    order_quantity   INT NOT NULL,
    total_amount     DECIMAL(10,2) NOT NULL,
    delivery_address VARCHAR(255) NOT NULL,
    delivery_date    DATE NOT NULL,
    order_status     VARCHAR(50) NOT NULL DEFAULT 'Open',
    PRIMARY KEY (order_id),
    FOREIGN KEY (customer_id)  REFERENCES Customer(customer_id),
    FOREIGN KEY (furniture_id) REFERENCES Furniture(furniture_id)
) ENGINE = InnoDB;


-- ============================================
-- SAMPLE DATA
-- ============================================

INSERT INTO Customer (customer_name, password, contact_number, address) VALUES
('Alice Chan',  'alice123',  '98001111', '10 Nathan Road, Kowloon'),
('Bob Lee',     'bob123',    '97002222', '20 Queen Road, HK Island'),
('Carol Wong',  'carol123',  '96003333', '30 Tuen Mun Road, NT'),
('David Cheung', 'david123', '95004444', '40 Waterloo Road, Kowloon'),
('Eva Ho', 'eva123', '94005555', '50 King''s Road, HK Island'),
('Frank Lam', 'frank123', '93006666', '60 Castle Peak Road, NT'),
('Grace Ng', 'grace123', '92007777', '70 Canton Road, Kowloon'),
('Henry Yip', 'henry123', '91008888', '80 Hennessy Road, HK Island'),
('Ivy Cheng', 'ivy123', '90009999', '90 Sha Tsui Road, NT'),
('Jacky Lau', 'jacky123', '68001010', '100 Argyle Street, Kowloon');

INSERT INTO Staff (staff_name, password) VALUES
('Admin Staff',  'admin123'),
('Manager',      'manager123');

INSERT INTO Material (material_name, physical_quantity, unit) VALUES
('Oak Wood',     200, 'kg'),
('Steel Frame',  150, 'pcs'),
('Foam Padding', 300, 'kg'),
('Fabric Cover', 100, 'meter'),
('Screws',       500, 'pcs');

INSERT INTO Furniture (furniture_name, description, image, price, stock_quantity) VALUES
('Oak Dining Table',  'Solid oak dining table for 6 persons',     NULL,   1200.00, 10),
('Steel Bookshelf',   '5-tier steel bookshelf, modern design',    NULL,    450.00, 15),
('Fabric Sofa',       '3-seater fabric sofa with foam padding',   NULL,   2500.00,  8),
('Oak Bed Frame',     'Queen-size solid oak bed frame',           NULL,   1800.00,  5),
('Study Chair',       'Ergonomic study chair with fabric cover',  'chair.jpg',        350.00, 20);

INSERT INTO Furniture_Material (furniture_id, material_id, material_quantity) VALUES
(1, 1, 30.00),  -- Oak Dining Table uses Oak Wood
(1, 5, 20.00),  -- Oak Dining Table uses Screws
(2, 2,  5.00),  -- Steel Bookshelf uses Steel Frame
(2, 5, 15.00),  -- Steel Bookshelf uses Screws
(3, 3, 10.00),  -- Fabric Sofa uses Foam Padding
(3, 4,  3.00),  -- Fabric Sofa uses Fabric Cover
(4, 1, 25.00),  -- Oak Bed Frame uses Oak Wood
(4, 5, 30.00),  -- Oak Bed Frame uses Screws
(5, 3,  2.00),  -- Study Chair uses Foam Padding
(5, 4,  1.50);  -- Study Chair uses Fabric Cover

INSERT INTO Orders (customer_id, furniture_id, order_date, order_quantity, total_amount, delivery_address, delivery_date, order_status) VALUES
(1, 1, '2026-06-01', 1, 1200.00, '10 Nathan Road, Kowloon',    '2026-06-15', 'Approved'),
(2, 3, '2026-06-05', 2, 5000.00, '20 Queen Road, HK Island',   '2026-06-20', 'Open'),
(3, 5, '2026-06-10', 3, 1050.00, '30 Tuen Mun Road, NT',       '2026-06-25', 'Open'),
(1, 2, '2026-06-12', 1,  450.00, '10 Nathan Road, Kowloon',    '2026-06-28', 'Rejected'),
(2, 4, '2026-06-15', 1, 1800.00, '20 Queen Road, HK Island',   '2026-06-30', 'Open');