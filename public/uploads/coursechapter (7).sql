-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 30, 2024 at 02:01 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `learningplatform`
--

-- --------------------------------------------------------

--
-- Table structure for table `coursechapter`
--

CREATE TABLE `coursechapter` (
  `CourseChapterID` varchar(15) DEFAULT NULL,
  `CourseChapterTitle` varchar(100) DEFAULT NULL,
  `CourseChapterDescription` text DEFAULT NULL,
  `CourseChapterVideoUrl` varchar(255) DEFAULT NULL,
  `CourseChapterContent` text DEFAULT NULL,
  `CourseID` varchar(10) DEFAULT NULL,
  `AssessmentID` varchar(10) DEFAULT NULL,
  `PracticalTestID` varchar(10) DEFAULT NULL,
  `CourseChapterImage` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coursechapter`
--

INSERT INTO `coursechapter` (`CourseChapterID`, `CourseChapterTitle`, `CourseChapterDescription`, `CourseChapterVideoUrl`, `CourseChapterContent`, `CourseID`, `AssessmentID`, `PracticalTestID`, `CourseChapterImage`) VALUES
('CH001', 'Introduction to HTML', 'Learn what HTML is, its importance in web development, and how to write your first HTML code.', 'https://example.com/intro-to-html', '<h2>What is HTML?</h2>\r\n<p>HTML (HyperText Markup Language) is the backbone of any website. It structures content for web browsers to display.</p>\r\n<h3>HTML Basics:</h3>\r\n<ul>\r\n  <li>HTML uses elements called <strong>tags</strong>.</li>\r\n  <li>Every HTML document starts with a <code>&lt;!DOCTYPE html&gt;</code>.</li>\r\n  <li>The <code>&lt;html&gt;</code> tag wraps all other tags.</li>\r\n</ul>\r\n<h3>Basic HTML Template:</h3>\r\n<pre>\r\n&lt;!DOCTYPE html&gt;\r\n&lt;html&gt;\r\n  &lt;head&gt;\r\n    &lt;title&gt;My First Webpage&lt;/title&gt;\r\n  &lt;/head&gt;\r\n  &lt;body&gt;\r\n    &lt;h1&gt;Welcome to HTML&lt;/h1&gt;\r\n    &lt;p&gt;This is my first HTML document.&lt;/p&gt;\r\n  &lt;/body&gt;\r\n&lt;/html&gt;\r\n</pre>', 'C001', 'A001', 'PT001', 'images/post-1-1.png'),
('CH002', 'HTML Text and Formatting', 'Learn to create and format text content using various HTML tags.', 'https://example.com/html-text-formatting', '<h2>Text Elements</h2>\r\n<p>HTML provides several tags for structuring text:</p>\r\n<ul>\r\n  <li><code>&lt;p&gt;</code>: Defines a paragraph.</li>\r\n  <li><code>&lt;h1&gt; to &lt;h6&gt;</code>: Defines headings of various levels.</li>\r\n  <li><code>&lt;b&gt;</code>: Bold text.</li>\r\n  <li><code>&lt;i&gt;</code>: Italic text.</li>\r\n</ul>\r\n<h3>Example:</h3>\r\n<pre>\r\n&lt;h1&gt;This is a Heading&lt;/h1&gt;\r\n&lt;p&gt;This is a paragraph with &lt;b&gt;bold text&lt;/b&gt;.&lt;/p&gt;\r\n</pre>', 'C001', 'A002', 'PT002', 'images/post-1-2.png'),
('CH003', 'Links and Images', 'Learn how to add navigation links and images to your web pages.', 'https://example.com/html-links-images', '<h2>HTML Links</h2>\r\n<p>The <code>&lt;a&gt;</code> tag defines a hyperlink:</p>\r\n<pre>\r\n&lt;a href=\"https://www.example.com\"&gt;Visit Example&lt;/a&gt;\r\n</pre>\r\n<h2>HTML Images</h2>\r\n<p>The <code>&lt;img&gt;</code> tag embeds an image:</p>\r\n<pre>\r\n&lt;img src=\"image.jpg\" alt=\"Description of image\"&gt;\r\n</pre>', 'C001', 'A003', 'PT003', 'images/post-1-3.png'),
('CH004', 'HTML Tables', 'Learn to structure data in tables using HTML.', 'https://example.com/html-tables', '<h2>Creating a Table</h2>\r\n<p>Use the <code>&lt;table&gt;</code> tag to create tables:</p>\r\n<pre>\r\n&lt;table&gt;\r\n  &lt;tr&gt;\r\n    &lt;th&gt;Header 1&lt;/th&gt;\r\n    &lt;th&gt;Header 2&lt;/th&gt;\r\n  &lt;/tr&gt;\r\n  &lt;tr&gt;\r\n    &lt;td&gt;Row 1, Col 1&lt;/td&gt;\r\n    &lt;td&gt;Row 1, Col 2&lt;/td&gt;\r\n  &lt;/tr&gt;\r\n&lt;/table&gt;\r\n</pre>', 'C001', 'A004', 'PT004', 'images/post-1-4.png'),
('CH005', 'HTML Forms', 'Understand how to create forms for user input.', 'https://example.com/html-forms', '<h2>Form Elements</h2>\r\n<p>Forms collect user input:</p>\r\n<pre>\r\n&lt;form action=\"submit.php\" method=\"post\"&gt;\r\n  &lt;label&gt;Name:&lt;/label&gt;\r\n  &lt;input type=\"text\" name=\"name\"&gt;\r\n  &lt;button type=\"submit\"&gt;Submit&lt;/button&gt;\r\n&lt;/form&gt;\r\n</pre>', 'C001', 'A005', 'PT005', 'images/post-1-5.png'),
('CH006', 'Semantic HTML', 'Learn the importance of semantic elements for accessibility and SEO.', 'https://example.com/semantic-html', '<h2>Semantic Tags</h2>\r\n<p>Semantic tags provide meaning to the HTML structure:</p>\r\n<ul>\r\n  <li><code>&lt;header&gt;</code>: Defines a header section.</li>\r\n  <li><code>&lt;nav&gt;</code>: Represents navigation links.</li>\r\n  <li><code>&lt;main&gt;</code>: Main content area.</li>\r\n</ul>\r\n<h3>Example:</h3>\r\n<pre>\r\n&lt;header&gt;Website Header&lt;/header&gt;\r\n&lt;main&gt;Main Content&lt;/main&gt;\r\n&lt;footer&gt;Website Footer&lt;/footer&gt;\r\n</pre>', 'C001', 'A006', 'PT006', 'images/post-1-6.png'),
('CH101', 'Introduction to CSS', 'Learn what CSS is, its purpose, and how to integrate it with HTML.', 'https://example.com/intro-to-css', '<h2>What is CSS?</h2>\r\n<p>CSS (Cascading Style Sheets) is used to style and layout web pages. It controls colors, fonts, spacing, and much more.</p>\r\n<h3>How to Add CSS:</h3>\r\n<ul>\r\n  <li>Inline CSS: Inside an HTML element using the <code>style</code> attribute.</li>\r\n  <li>Internal CSS: Within a <code>&lt;style&gt;</code> tag inside the <code>&lt;head&gt;</code>.</li>\r\n  <li>External CSS: In a separate file with a <code>.css</code> extension linked to the HTML.</li>\r\n</ul>\r\n<h3>Basic CSS Syntax:</h3>\r\n<pre>\r\nselector {\r\n  property: value;\r\n}\r\n</pre>\r\n<h3>Example:</h3>\r\n<pre>\r\nbody {\r\n  background-color: lightblue;\r\n}\r\n</pre>', 'CSS101', 'A101', 'PT101', 'images/post-2-1.png'),
('CH102', 'CSS Selectors', 'Learn the various ways to target and style HTML elements using CSS selectors.', 'https://example.com/css-selectors', '<h2>What are CSS Selectors?</h2>\r\n<p>Selectors are used to \"select\" the elements you want to style.</p>\r\n<h3>Types of Selectors:</h3>\r\n<ul>\r\n  <li><strong>Universal Selector</strong>: <code>*</code></li>\r\n  <li><strong>Type Selector</strong>: Targets HTML tags, e.g., <code>p</code>, <code>h1</code>.</li>\r\n  <li><strong>Class Selector</strong>: Targets elements with a specific class, e.g., <code>.classname</code>.</li>\r\n  <li><strong>ID Selector</strong>: Targets elements with a specific ID, e.g., <code>#idname</code>.</li>\r\n</ul>\r\n<h3>Example:</h3>\r\n<pre>\r\np {\r\n  color: blue;\r\n}\r\n\r\n.classname {\r\n  font-size: 20px;\r\n}\r\n\r\n#idname {\r\n  text-align: center;\r\n}\r\n</pre>', 'CSS101', 'A102', 'PT102', 'images/post-2-2.png'),
('CH103', 'CSS Box Model', 'Understand the CSS box model and how it affects the layout of elements.', 'https://example.com/css-box-model', '<h2>What is the CSS Box Model?</h2>\r\n<p>Every HTML element is treated as a rectangular box. The box model consists of:</p>\r\n<ul>\r\n  <li><strong>Content</strong>: The actual content of the element (e.g., text, images).</li>\r\n  <li><strong>Padding</strong>: Space between the content and the border.</li>\r\n  <li><strong>Border</strong>: A border around the padding.</li>\r\n  <li><strong>Margin</strong>: Space outside the border.</li>\r\n</ul>\r\n<h3>Box Model Example:</h3>\r\n<pre>\r\ndiv {\r\n  width: 200px;\r\n  padding: 10px;\r\n  border: 5px solid black;\r\n  margin: 20px;\r\n}\r\n</pre>\r\n<h3>Visualization:</h3>\r\n<p>The total width of an element = content width + padding + border + margin.</p>', 'CSS101', 'A103', 'PT103', 'images/post-2-3.png'),
('CH104', 'CSS Positioning', 'Learn how to position elements on a webpage using CSS.', 'https://example.com/css-positioning', '<h2>CSS Positioning Properties</h2>\r\n<p>CSS provides several ways to position elements:</p>\r\n<ul>\r\n  <li><strong>Static</strong>: Default positioning.</li>\r\n  <li><strong>Relative</strong>: Position relative to its normal position.</li>\r\n  <li><strong>Absolute</strong>: Position relative to its nearest positioned ancestor.</li>\r\n  <li><strong>Fixed</strong>: Position relative to the viewport.</li>\r\n</ul>\r\n<h3>Example:</h3>\r\n<pre>\r\ndiv {\r\n  position: absolute;\r\n  top: 50px;\r\n  left: 100px;\r\n}\r\n</pre>', 'CSS101', 'A104', 'PT104', 'images/post-2-4.png'),
('CH105', 'CSS Flexbox', 'Master the CSS Flexbox layout model for flexible and responsive designs.', 'https://example.com/css-flexbox', '<h2>What is Flexbox?</h2>\r\n<p>The Flexible Box Layout, or Flexbox, is a layout model for arranging items in a container.</p>\r\n<h3>Important Properties:</h3>\r\n<ul>\r\n  <li><strong>display</strong>: Set to <code>flex</code>.</li>\r\n  <li><strong>justify-content</strong>: Aligns items horizontally (e.g., <code>center</code>, <code>space-between</code>).</li>\r\n  <li><strong>align-items</strong>: Aligns items vertically (e.g., <code>center</code>, <code>stretch</code>).</li>\r\n  <li><strong>flex-direction</strong>: Defines the direction (row or column).</li>\r\n</ul>\r\n<h3>Example:</h3>\r\n<pre>\r\n.container {\r\n  display: flex;\r\n  justify-content: center;\r\n  align-items: center;\r\n}\r\n</pre>', 'CSS101', 'A105', 'PT105', 'images/post-2-5.png'),
('CH106', 'CSS Grid', 'Learn how to use the CSS Grid layout for advanced and precise layouts.', 'https://example.com/css-grid', '<h2>What is CSS Grid?</h2>\r\n<p>The CSS Grid Layout is a two-dimensional layout system for arranging items into rows and columns.</p>\r\n<h3>Important Properties:</h3>\r\n<ul>\r\n  <li><strong>display</strong>: Set to <code>grid</code>.</li>\r\n  <li><strong>grid-template-rows</strong> and <strong>grid-template-columns</strong>: Define row and column sizes.</li>\r\n  <li><strong>grid-gap</strong>: Adds space between grid items.</li>\r\n</ul>\r\n<h3>Example:</h3>\r\n<pre>\r\n.container {\r\n  display: grid;\r\n  grid-template-columns: 1fr 1fr;\r\n  grid-gap: 10px;\r\n}\r\n</pre>', 'CSS101', 'A106', 'PT106', 'images/post-2-6.png'),
('CH301', 'yeway', NULL, NULL, 'yeway', 'C003', NULL, NULL, 'download (1).jpeg');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
