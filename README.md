# Internet Souvenir Shop

The purpose of this project is to create a convenient platform for browsing, selecting, and purchasing souvenir products, allowing for the practice of design principles and design patterns implementation.

## DESIGN PATTERNS

### Singleton Pattern
The project utilizes the Singleton pattern for database connection. This ensures only one instance of the database connection exists, minimizing unnecessary connections and optimizing database operations.
[Singleton](https://github.com/anne-stupakova/designPatterns_lab-6/blob/master/db/DatabaseConnection.php)

### Proxy Pattern
The Proxy pattern is used for caching database queries, reducing the number of queries and improving application performance. Cached data eliminates the need for direct database access for subsequent queries.
[Proxy](https://github.com/anne-stupakova/designPatterns_lab-6/blob/master/views/index.php)

### Strategy Pattern
For password hashing strategies, the project employs the Strategy pattern. This allows for using different hashing methods based on requirements without modifying the code significantly.
[Strategy](https://github.com/anne-stupakova/designPatterns_lab-6/blob/master/views/PasswordStrategy.php)

### Command Pattern
The Command pattern is utilized for order functionality. Each order is encapsulated in a command object, which handles order actions, stores them in the database, and provides undo functionalities.
[Command](https://github.com/anne-stupakova/designPatterns_lab-6/blob/master/views/add_to_order.php)

# PROGRAMMING RRINCIPLES

## DRY - Don't Repeat Yourself
The project adheres to the DRY principle, avoiding code duplication. For example, the general code for connecting to the database is encapsulated in the [Singleton](https://github.com/anne-stupakova/designPatterns_lab-6/blob/master/db/DatabaseConnection.php) pattern, which ensures that the logic for connecting to the database is not repeated in different parts of the application. Or putting the header and footer in separate [files](https://github.com/anne-stupakova/designPatterns_lab-6/tree/master/wrapper) so that you don't have to write them every time on the page.

## KISS - Keep it Simple, Stupid
The KISS principle is followed to maintain simplicity in code design and implementation. For instance, the user [authentication](https://github.com/anne-stupakova/designPatterns_lab-6/blob/master/views/login.php) mechanisms are kept straightforward and easy to understand, without unnecessary complexity.

## YAGNI - You Aren't Gonna Need It
The YAGNI principle is applied by avoiding premature optimisation or adding features that are not needed at the moment. This helped me focus the code base on core functionality, reducing unnecessary complexity and potential bugs.

## Fail Fast
The Fail Fast principle is implemented in error handling mechanisms. For instance, when handling user inputs such as [order](https://github.com/anne-stupakova/designPatterns_lab-6/blob/master/views/add_to_order.php) form data, input [validation](https://github.com/anne-stupakova/designPatterns_lab-6/blob/master/views/register.php) is performed early to catch errors quickly and provide meaningful feedback to users.

## Single-responsibility Principle
The Single-responsibility Principle is applied throughout the project to ensure that each class or module has a single responsibility. For example, the [DatabaseFetcher](https://github.com/anne-stupakova/designPatterns_lab-6/blob/00e201ddaa510320eba2c8e44d78ced43c0ab8df/views/index.php#L17) class is responsible for fetching data from the database, while the [ProxyFetcher](https://github.com/anne-stupakova/designPatterns_lab-6/blob/00e201ddaa510320eba2c8e44d78ced43c0ab8df/views/index.php#L35) class handles caching data to improve performance, keeping responsibilities separate and well-defined.

## Interface Segregation Principle
The principle of separation of interfaces is reflected in the design of interfaces and classes. Interfaces were designed to be specific and tailored to the needs of the classes that implement them. For example, the [DataFetcher](https://github.com/anne-stupakova/designPatterns_lab-6/blob/00e201ddaa510320eba2c8e44d78ced43c0ab8df/views/index.php#L10) interface defines methods for selecting categories and products separately, following the principle of segregation based on the client's needs.