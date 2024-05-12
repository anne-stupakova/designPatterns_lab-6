# Internet Souvenir Shop "Memento"

The aim of the project is to create a convenient platform for viewing, selecting and purchasing souvenirs, allowing you to practice design principles, implement design patterns and refactoring techniques.

## Functionality of the online souvenir shop

#### Home Page:
- Display main products categorized by category.
- Search for products by name.
- Display selected products.

#### Placing an Order:
- Enter customer data (name, city, branch number, phone number).
- Confirm the order and save it in the database.

#### Personal Account:
- Registration of new users or login for existing users.
- View order history.
- Change personal data (address, email).
- Log out of your account.

#### About the Company:
- Simply display information about the company and contacts.

### Running the Application Locally

To run the application locally, follow these steps:

1. Download the project from the repository.
2. Import the [database](https://github.com/anne-stupakova/designPatterns_lab-6/blob/master/db/kpz_db.sql) into MySQL.
3. Open the project in the development environment of your choice.
4. Run the main application [file](https://github.com/anne-stupakova/designPatterns_lab-6/blob/master/views/index.php).

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

## PROGRAMMING RRINCIPLES

### DRY - Don't Repeat Yourself
The project adheres to the DRY principle, avoiding code duplication. For example, the general code for connecting to the database is encapsulated in the [Singleton](https://github.com/anne-stupakova/designPatterns_lab-6/blob/master/db/DatabaseConnection.php) pattern, which ensures that the logic for connecting to the database is not repeated in different parts of the application. Or putting the header and footer in separate [files](https://github.com/anne-stupakova/designPatterns_lab-6/tree/master/wrapper) so that you don't have to write them every time on the page.

### KISS - Keep it Simple, Stupid
The KISS principle is followed to maintain simplicity in code design and implementation. For instance, the user [authentication](https://github.com/anne-stupakova/designPatterns_lab-6/blob/master/views/login.php) mechanisms are kept straightforward and easy to understand, without unnecessary complexity.

### YAGNI - You Aren't Gonna Need It
The YAGNI principle is applied by avoiding premature optimisation or adding features that are not needed at the moment. This helped me focus the code base on core functionality, reducing unnecessary complexity and potential bugs.

### Fail Fast
The Fail Fast principle is implemented in error handling mechanisms. For instance, when handling user inputs such as [order](https://github.com/anne-stupakova/designPatterns_lab-6/blob/master/views/add_to_order.php) form data, input [validation](https://github.com/anne-stupakova/designPatterns_lab-6/blob/master/views/register.php) is performed early to catch errors quickly and provide meaningful feedback to users.

### Single-responsibility Principle
The Single-responsibility Principle is applied throughout the project to ensure that each class or module has a single responsibility. For example, the [DatabaseFetcher](https://github.com/anne-stupakova/designPatterns_lab-6/blob/00e201ddaa510320eba2c8e44d78ced43c0ab8df/views/index.php#L17) class is responsible for fetching data from the database, while the [ProxyFetcher](https://github.com/anne-stupakova/designPatterns_lab-6/blob/00e201ddaa510320eba2c8e44d78ced43c0ab8df/views/index.php#L35) class handles caching data to improve performance, keeping responsibilities separate and well-defined.

### Interface Segregation Principle
The principle of separation of interfaces is reflected in the design of interfaces and classes. Interfaces were designed to be specific and tailored to the needs of the classes that implement them. For example, the [DataFetcher](https://github.com/anne-stupakova/designPatterns_lab-6/blob/00e201ddaa510320eba2c8e44d78ced43c0ab8df/views/index.php#L10) interface defines methods for selecting categories and products separately, following the principle of segregation based on the client's needs.

## REFACTORING TECHNIQUES

### Simplifying Method Calls

During the refactoring process, several methods were streamlined by consolidating related actions into single functions or adopting clearer method names. This optimization enhanced code readability and usability significantly.

### Simplification of Conditional Expressions

As part of the refactoring efforts, conditional expressions were simplified using the ternary operator and other concise techniques. This approach reduced the complexity of logical structures and improved code clarity and readability. For instance, the validation logic for email format and password length in the user registration function was revamped using the ternary operator, resulting in a more concise and understandable condition.

### Data Organisation

An essential aspect of the refactoring initiative was the systematic organization of data through the implementation of models and classes. This structural enhancement facilitated better management of project data, minimizing reliance on specific data formats and enhancing processing efficiency. For example, the utilization of the UserMod class for encapsulating user data (such as name, email, password) offered a structured and user-friendly approach to storing and manipulating this information.
