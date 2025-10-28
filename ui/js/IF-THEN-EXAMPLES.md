# if-then.js - Usage Examples

## Quick Start

The `if-then.js` library is now available globally via `window.IT` or `window.__IF_THEN__`.

## Type Checking

```javascript
// Check if value is a specific type
IT.type.isString("hello");      // true
IT.type.isNumber(42);            // true
IT.type.isArray([1, 2, 3]);      // true
IT.type.isObject({foo: 'bar'});  // true
IT.type.isEmptyString("");        // true
IT.type.isEmptyArray([]);         // true
```

## Value Checking

```javascript
// Check if value exists and has properties
IT.check.exists(null);           // false
IT.check.hasLength("hello");     // true
IT.check.inRange(5, 1, 10);     // true
IT.check.equals(5, 5);           // true
IT.check.greaterThan(10, 5);    // true
```

## Safe Operations

```javascript
// Safely get nested properties
const user = { profile: { name: "John" } };
IT.safe.get(user, 'profile.name', 'Anonymous'); // "John"

// Safely call functions
const result = IT.safe.call(myFunction, thisContext, arg1, arg2);

// Try-catch wrapper
const data = IT.safe.try(() => {
    return riskyOperation();
}, "fallback value");
```

## Conditional Logic

```javascript
// If-then logic
IT.if.when(userIsLoggedIn, () => {
    showUserMenu();
}, () => {
    showLoginButton();
});

// Chain conditions
const priority = IT.if.chain(
    [isUrgent(task), 'urgent'],
    [isImportant(task), 'important'],
    [true, 'normal']
);

// Switch-like matching
IT.if.match(status, {
    'pending': () => showPending(),
    'completed': () => showCompleted(),
    'failed': () => showError()
}, () => showDefault());
```

## Validation

```javascript
// Email validation
IT.validate.isEmail("test@example.com"); // true

// URL validation
IT.validate.isURL("https://example.com"); // true

// Password strength
IT.validate.isStrongPassword("SecureP@ss123"); // true

// Required fields
IT.validate.isRequired(formData.email); // true/false

// Length validation
IT.validate.hasMinLength("password", 8); // true/false
```

## DOM Utilities

```javascript
// Safely query elements
const button = IT.dom.query('#my-button');
const allButtons = IT.dom.queryAll('.button');

// Wait for element to appear
const modal = await IT.dom.waitFor('#modal', 5000);

// Add event listeners
IT.dom.on(button, 'click', () => {
    console.log('Clicked!');
});

// Toggle classes
IT.dom.toggleClass(element, 'active');
IT.dom.addClass(element, 'visible');
IT.dom.hasClass(element, 'visible'); // true/false
```

## Array Utilities

```javascript
const users = [
    { id: 1, name: 'John', role: 'admin' },
    { id: 2, name: 'Jane', role: 'user' },
    { id: 3, name: 'Bob', role: 'admin' }
];

// Find item
IT.array.findMatch(users, { role: 'admin' }); // { id: 1, ... }

// Remove duplicates
IT.array.unique([1, 2, 2, 3]); // [1, 2, 3]

// Group by property
IT.array.groupBy(users, 'role');
// { admin: [...], user: [...] }

// Sort array
IT.array.sortBy(users, 'name', 'asc');

// Flatten nested arrays
IT.array.flatten([1, [2, 3], [4]]); // [1, 2, 3, 4]

// Chunk array
IT.array.chunk([1, 2, 3, 4, 5], 2); // [[1, 2], [3, 4], [5]]
```

## Object Utilities

```javascript
const user = { id: 1, name: 'John', role: 'admin' };

// Merge objects
IT.object.merge(user, { email: 'john@example.com' });

// Clone object
const cloned = IT.object.clone(user);

// Pick specific keys
IT.object.pick(user, ['id', 'name']); // { id: 1, name: 'John' }

// Omit specific keys
IT.object.omit(user, ['role']); // { id: 1, name: 'John' }

// Check for keys
IT.object.hasKeys(user, ['id', 'name']); // true
```

## Promise Utilities

```javascript
// Retry failed operations
await IT.promise.retry(() => fetchData(), 3, 1000);

// Add timeout to promises
await IT.promise.timeout(fetchData(), 5000);

// Add delay
await IT.promise.delay(1000); // wait 1 second
```

## Storage

```javascript
// LocalStorage with JSON support
IT.storage.set('user', { id: 1, name: 'John' });
const user = IT.storage.get('user', {});

// SessionStorage
IT.storage.session.set('token', 'abc123');
const token = IT.storage.session.get('token');

// Remove/clear
IT.storage.remove('user');
IT.storage.clear();
```

## Debounce & Throttle

```javascript
// Debounce - wait for inactivity
const search = IT.timing.debounce((value) => {
    console.log('Searching for:', value);
}, 300);
search(input.value);

// Throttle - limit execution rate
const scroll = IT.timing.throttle((e) => {
    console.log('Scrolled!');
}, 100);
window.addEventListener('scroll', scroll);
```

## Real-World Example

```javascript
// Check if user data is valid before saving
const userData = {
    email: 'test@example.com',
    password: 'SecureP@ss123',
    age: 25
};

if (IT.if.all(
    () => IT.validate.isEmail(userData.email),
    () => IT.validate.isRequired(userData.password),
    () => IT.check.greaterOrEqual(userData.age, 18)
)) {
    // All validations passed
    IT.safe.call(() => {
        IT.storage.set('user', userData);
        showSuccessToast();
    });
} else {
    showValidationErrors();
}
```

## Integration with Existing Code

The library is completely non-invasive and won't break any existing functionality. It's safe to use alongside your current `main.js` code:

```javascript
// Your existing code continues to work
document.addEventListener('DOMContentLoaded', () => {
    // ...
});

// Now you can also use IT utilities
const button = IT.dom.query('#my-button');
if (IT.type.isElement(button)) {
    IT.dom.on(button, 'click', () => {
        // Your code here
    });
}
```

