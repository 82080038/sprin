<?php

declare(strict_types=1);

/**
 * Split to Explode Migration Example
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

echo '<h1>Split to Explode Migration Example</h1>';

echo '<h2>Before (explode() - DEPRECATED):</h2>';
echo '<pre><code>';
echo '// Old way - DEPRECATED
$string = "apple,banana,orange";
$fruits = explode(",", $string);  // DEPRECATED!

foreach ($fruits as $fruit) {
    echo $fruit . "\n";
}
</code></pre>';

echo '<h2>After (explode() - MODERN):</h2>';
echo '<pre><code>';
echo '// New way - MODERN
$string = "apple,banana,orange";
$fruits = explode(",", $string);  // MODERN!

foreach ($fruits as $fruit) {
    echo $fruit . "\n";
}
</code></pre>';

// Demonstration
$string = "apple,banana,orange,grape,kiwi";

echo '<h2>Live Demo:</h2>';
echo '<p>Original string: <code>' . htmlspecialchars($string) . '</code></p>';

echo '<h3>Using explode():</h3>';
$fruits = explode(",", $string);
echo '<ul>';
foreach ($fruits as $fruit) {
    echo '<li>' . htmlspecialchars($fruit) . '</li>';
}
echo '</ul>';

echo '<h2>Benefits of explode():</h2>';
echo '<ul>';
echo '<li>✅ Faster performance</li>';
echo '<li>✅ More consistent behavior</li>';
echo '<li>✅ Better documentation</li>';
echo '<li>✅ Not deprecated</li>';
echo '</ul>';

echo '<h2>Migration Steps:</h2>';
echo '<ol>';
echo '<li>Replace explode() with explode()</li>';
echo '<li>Test the behavior (should be identical)</li>';
echo '<li>Remove any regex patterns if not needed</li>';
echo '<li>Consider using preg_explode() for complex patterns</li>';
echo '</ol>';

// Additional examples
echo '<h2>Additional Examples:</h2>';
echo '<h3>Exploding by space:</h3>';
$sentence = "Hello world how are you";
$words = explode(" ", $sentence);
echo '<code>' . htmlspecialchars($sentence) . '</code> → <ul>';
foreach ($words as $word) {
    echo '<li>' . htmlspecialchars($word) . '</li>';
}
echo '</ul>';

echo '<h3>Exploding by newline:</h3>';
$multiline = "Line 1\nLine 2\nLine 3";
$lines = explode("\n", $multiline);
echo '<pre>' . htmlspecialchars($multiline) . '</pre> → <ul>';
foreach ($lines as $line) {
    echo '<li>' . htmlspecialchars($line) . '</li>';
}
echo '</ul>';
?>
