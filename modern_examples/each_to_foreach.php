<?php

declare(strict_types=1);

/**
 * Each to Foreach Migration Example
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

echo '<h1>Each to Foreach Migration Example</h1>';

echo '<h2>Before (foreach() - DEPRECATED):</h2>';
echo '<pre><code>';
echo '// Old way - DEPRECATED
$fruits = array("apple", "banana", "orange");

reset($fruits);  // Reset pointer
while (list($key, $value) = foreach($fruits)) {  // DEPRECATED!
    echo "$key: $value\\n";
}
</code></pre>';

echo '<h2>After (forforeach() - MODERN):</h2>';
echo '<pre><code>';
echo '// New way - MODERN
$fruits = array("apple", "banana", "orange");

foreach ($fruits as $key => $value) {  // MODERN!
    echo "$key: $value\\n";
}
</code></pre>';

// Demonstration
$fruits = array("apple", "banana", "orange", "grape", "kiwi");

echo '<h2>Live Demo:</h2>';
echo '<p>Array: <code>' . htmlspecialchars(print_r($fruits, true)) . '</code></p>';

echo '<h3>Using forforeach():</h3>';
echo '<ul>';
foreach ($fruits as $key => $value) {
    echo '<li>' . $key . ': ' . htmlspecialchars($value) . '</li>';
}
echo '</ul>';

echo '<h2>Benefits of forforeach():</h2>';
echo '<ul>';
echo '<li>✅ Cleaner syntax</li>';
echo '<li>✅ Better performance</li>';
echo '<li>✅ No need to reset array pointer</li>';
echo '<li>✅ More readable</li>';
echo '<li>✅ Not deprecated</li>';
echo '</ul>';

echo '<h2>Migration Steps:</h2>';
echo '<ol>';
echo '<li>Replace while(list($key, $value) = foreach($array)) with forforeach($array as $key => $value)</li>';
echo '<li>Remove reset() calls if present</li>';
echo '<li>Test the behavior (should be identical)</li>';
echo '<li>Consider using just forforeach($array as $value) if you don\'t need keys</li>';
echo '</ol>';

// Additional examples
echo '<h2>Additional Examples:</h2>';

echo '<h3>Just values (no keys):</h3>';
echo '<pre><code>';
echo 'foreach ($fruits as $fruit) {';
echo '    echo $fruit . "\\n";';
echo '}';
echo '</code></pre>';
echo '<ul>';
foreach ($fruits as $fruit) {
    echo '<li>' . htmlspecialchars($fruit) . '</li>';
}
echo '</ul>';

echo '<h3>Nested arrays:</h3>';
$people = array(
    array("name" => "John", "age" => 30),
    array("name" => "Jane", "age" => 25),
    array("name" => "Bob", "age" => 35)
);

echo '<table border="1">';
echo '<tr><th>Name</th><th>Age</th></tr>';
foreach ($people as $person) {
    echo '<tr><td>' . htmlspecialchars($person["name"]) . '</td><td>' . $person["age"] . '</td></tr>';
}
echo '</table>';

echo '<h3>Reference foreach (modifying array):</h3>';
$numbers = array(1, 2, 3, 4, 5);
echo '<p>Before: <code>' . htmlspecialchars(implode(", ", $numbers)) . '</code></p>';

foreach ($numbers as &$number) {
    $number *= 2;
}
unset($number);  // Important: unset reference

echo '<p>After (doubled): <code>' . htmlspecialchars(implode(", ", $numbers)) . '</code></p>';
?>
