CREATE DATABASE optica;

USE optica;


-- Table: Sucursal
CREATE TABLE Sucursal (
    IdS INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(255),
    Direccion VARCHAR(255),
    Correo VARCHAR(255)
);

-- Table: Producto
CREATE TABLE Producto (
    IdP INT(5) PRIMARY KEY AUTO_INCREMENT,
    Tipo ENUM('Oftálmico', 'Solar', 'Contacto'),
    Precio DECIMAL(7, 2),
    Disponible BOOLEAN
);

-- Table: OrdenCompra
CREATE TABLE OrdenCompra (
    IdOC INT(5) PRIMARY KEY AUTO_INCREMENT,
    TotalGlobal DECIMAL(9, 2),
    fechaCompra DATETIME  -- Changed to DATETIME
);

-- Table: Venta
CREATE TABLE Venta (
    IdV INT(5) PRIMARY KEY AUTO_INCREMENT,
    MetodoVenta BOOLEAN,  -- Clarified BOOLEAN
    Entrega DATETIME,  -- Changed to DATETIME
    Cliente VARCHAR(255),
    TelefonoCliente INT(15),
    FK_IdSucursal INT (5),
    FOREIGN KEY (FK_IdSucursal) REFERENCES Sucursal(IdS)
);

-- Table: Relacion_OC_P (Relación Muchos a Muchos entre OrdenCompra y Producto)
CREATE TABLE Relacion_OC_P (
    IdOC_P INT (5) PRIMARY KEY AUTO_INCREMENT,
    FK_IdOC INT (5),
    FK_IdP int(5),
    FOREIGN KEY (FK_IdOC) REFERENCES OrdenCompra(IdOC),
    FOREIGN KEY (FK_IdP) REFERENCES Producto(IdP)
);

-- Table: Relacion_V_OC (Relación entre Venta y OrdenCompra para detalles de pago)
CREATE TABLE Relacion_V_OC (
    IdV_OC INT (5) PRIMARY KEY AUTO_INCREMENT,
    FK_IdOC INT (5),
    FK_IdV INT(5),
    MetodoPago ENUM('Efectivo', 'Credito', 'Debito'),
    PagoInicial DECIMAL(5, 2),
    Estado BOOLEAN,  
    Deuda DECIMAL(5, 2),
    FechaPago DATETIME, 
    FOREIGN KEY (FK_IdOC) REFERENCES OrdenCompra(IdOC),
    FOREIGN KEY (FK_IdV) REFERENCES Venta(IdV)
);

ALTER TABLE Sucursal ADD Telefono VARCHAR(15);

INSERT INTO Sucursal (IdS, Nombre, Telefono, Direccion, Correo) VALUES
(1, 'Kalá', '981-100-2412', 'Predio s/n por Av. Humberto Lanz Cárdenas Siglo XXIII, Av. Exhacienda Kala, 24085 San Francisco de Campeche, Camp.', 'opticakala@gmail.com'),
(2, 'Centro', '981-115-1270', 'C. 10 entre 61 y 63, Zona Centro, 24000 San Francisco de Campeche, Camp.', 'opticacentro@gmail.com'),
(3, 'Lerma', '981-115-4756', 'C. 19, Lerma Centro, 24500 Lerma, Camp.', 'opticalerma@gmail.com');

INSERT INTO Producto (Tipo, Precio, Disponible) VALUES
('Oftálmico', 1300.00, 1),
('Solar', 800.00, 0),
('Contacto', 1200.00, 1),
('Solar', 1000.00, 1);

INSERT INTO OrdenCompra (TotalGlobal, fechaCompra) VALUES
(3700.00, '2025-02-09'),
(1300.00, '2025-02-10');

ALTER TABLE Venta MODIFY TelefonoCliente VARCHAR(20);

INSERT INTO Venta (MetodoVenta, Entrega, Cliente, TelefonoCliente, FK_IdSucursal) VALUES
(0, '2025-02-16', 'Jonathan', 9811231541, 1),
(1, '2025-02-17', 'David', 11111111, 2);

INSERT INTO Relacion_OC_P (FK_IdOC, FK_IdP) VALUES
(1, 3),
(1, 2),
(1, 3),
(2, 1);

ALTER TABLE Relacion_V_OC MODIFY PagoInicial DECIMAL(7, 2);
ALTER TABLE Relacion_V_OC MODIFY Deuda DECIMAL(7, 2);

INSERT INTO Relacion_V_OC (FK_IdOC, FK_IdV, MetodoPago, PagoInicial, Estado, Deuda, FechaPago) VALUES
(1, 1, 'Efectivo', 1500.00, 0, 2200.00, '2025-02-09'),
(2, 2, 'Debito', 1300.00, 1, 0.00, '2025-02-10'),
(1, 1, 'Credito', 2200.00, 1, 0.00, '2025-02-20');


