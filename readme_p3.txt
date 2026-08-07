1. Write a query to get Product name and quantity/unit
    SELECT 
        ProductName,
        QuantityPerUnit
    FROM Products;

2. Write a query to get current Product list (Product ID and name).
    SELECT 
        ProductID,
        ProductName
    FROM Products
    WHERE Discontinued = 0;

3. Write a query to get most expense and least expensive Product list (name and
unit price).
 most expensive:
    SELECT 
        ProductName,
        UnitPrice
    FROM Products
    WHERE UnitPrice = (
        SELECT MAX(UnitPrice)
        FROM Products
    );
 least expensive:
    SELECT 
        ProductName,
        UnitPrice
    FROM Products
    WHERE UnitPrice = (
        SELECT MIN(UnitPrice)
        FROM Products
    );

4. Write a query to get Product list (name, unit price) of above average price.
    SELECT
        ProductName,
        UnitPrice
    FROM Products
    WHERE UnitPrice > (
        SELECT AVG(UnitPrice)
        FROM Products
    );

5. Write a query to get Product list (id, name, unit price) where current products cost
less than $20
    SELECT
        ProductID,
        ProductName,
        UnitPrice
    FROM Products
    WHERE UnitPrice < 20
    AND Discontinued = 0;

6. Write a query to get Product list (name, units on order , units in stock) of stock is
less than the quantity on order.
    SELECT
        ProductName,
        UnitsOnOrder,
        UnitsInStock
    FROM Products
    WHERE UnitsInStock < UnitsOnOrder;