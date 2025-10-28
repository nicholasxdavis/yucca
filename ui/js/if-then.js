/**
 * if-then.js - Essential Utilities and If-Then Logic
 * A comprehensive utility library for conditional operations and safe data handling
 */

(function() {
    'use strict';

    /**
     * ===========================================
     * TYPE CHECKING UTILITIES
     * ===========================================
     */

    const TypeChecker = {
        isString: (value) => typeof value === 'string',
        isNumber: (value) => typeof value === 'number' && !isNaN(value),
        isInteger: (value) => Number.isInteger(value),
        isFloat: (value) => typeof value === 'number' && value % 1 !== 0,
        isBoolean: (value) => typeof value === 'boolean',
        isArray: (value) => Array.isArray(value),
        isObject: (value) => value !== null && typeof value === 'object' && !Array.isArray(value),
        isFunction: (value) => typeof value === 'function',
        isNull: (value) => value === null,
        isUndefined: (value) => typeof value === 'undefined',
        isDate: (value) => value instanceof Date,
        isElement: (value) => value instanceof Element,
        isNodeList: (value) => value instanceof NodeList,
        
        // Compound checks
        isNumeric: (value) => !isNaN(parseFloat(value)) && isFinite(value),
        isEmptyString: (value) => typeof value === 'string' && value.trim() === '',
        isEmptyArray: (value) => Array.isArray(value) && value.length === 0,
        isEmptyObject: (value) => isObject(value) && Object.keys(value).length === 0,
        
        // Value category checks
        isTruthy: (value) => Boolean(value),
        isFalsy: (value) => !Boolean(value),
        isEmpty: (value) => {
            if (value === null || value === undefined) return true;
            if (TypeChecker.isString(value)) return TypeChecker.isEmptyString(value);
            if (TypeChecker.isArray(value)) return TypeChecker.isEmptyArray(value);
            if (TypeChecker.isObject(value)) return TypeChecker.isEmptyObject(value);
            return false;
        }
    };

    /**
     * ===========================================
     * VALUE CHECKING UTILITIES
     * ===========================================
     */

    const ValueChecker = {
        exists: (value) => value !== null && value !== undefined,
        hasLength: (value) => {
            if (TypeChecker.isString(value)) return value.length > 0;
            if (TypeChecker.isArray(value)) return value.length > 0;
            if (TypeChecker.isObject(value)) return Object.keys(value).length > 0;
            return false;
        },
        inRange: (value, min, max) => value >= min && value <= max,
        isBetween: (value, min, max) => value > min && value < max,
        equals: (a, b) => a === b,
        notEquals: (a, b) => a !== b,
        greaterThan: (value, threshold) => value > threshold,
        lessThan: (value, threshold) => value < threshold,
        greaterOrEqual: (value, threshold) => value >= threshold,
        lessOrEqual: (value, threshold) => value <= threshold
    };

    /**
     * ===========================================
     * SAFE OPERATION UTILITIES
     * ===========================================
     */

    const SafeOperations = {
        // Safely get nested property
        get: (obj, path, defaultValue = null) => {
            if (!TypeChecker.isObject(obj)) return defaultValue;
            const keys = path.split('.');
            let current = obj;
            
            for (const key of keys) {
                if (!TypeChecker.isObject(current) || current[key] === undefined) {
                    return defaultValue;
                }
                current = current[key];
            }
            
            return current;
        },
        
        // Safely set nested property
        set: (obj, path, value) => {
            if (!TypeChecker.isObject(obj)) return false;
            const keys = path.split('.');
            let current = obj;
            
            for (let i = 0; i < keys.length - 1; i++) {
                if (!TypeChecker.isObject(current[keys[i]])) {
                    current[keys[i]] = {};
                }
                current = current[keys[i]];
            }
            
            current[keys[keys.length - 1]] = value;
            return true;
        },
        
        // Safely call a function
        call: (func, context, ...args) => {
            if (TypeChecker.isFunction(func)) {
                try {
                    return func.apply(context, args);
                } catch (error) {
                    console.error('Safe call error:', error);
                    return null;
                }
            }
            return null;
        },
        
        // Safely execute async function
        callAsync: async (func, context, ...args) => {
            if (TypeChecker.isFunction(func)) {
                try {
                    return await func.apply(context, args);
                } catch (error) {
                    console.error('Safe async call error:', error);
                    return null;
                }
            }
            return null;
        },
        
        // Try-catch wrapper
        try: (func, fallback = null) => {
            try {
                return func();
            } catch (error) {
                console.error('Operation failed:', error);
                return fallback;
            }
        },
        
        // Try-catch wrapper for async operations
        tryAsync: async (func, fallback = null) => {
            try {
                return await func();
            } catch (error) {
                console.error('Async operation failed:', error);
                return fallback;
            }
        }
    };

    /**
     * ===========================================
     * CONDITIONAL LOGIC UTILITIES
     * ===========================================
     */

    const IfThen = {
        // If condition is true, run thenFunc, else run elseFunc
        when: (condition, thenFunc, elseFunc = null) => {
            if (condition) {
                return TypeChecker.isFunction(thenFunc) ? thenFunc() : thenFunc;
            }
            if (TypeChecker.isFunction(elseFunc)) {
                return elseFunc();
            }
            return elseFunc;
        },
        
        // Opposite of when - runs when condition is false
        unless: (condition, thenFunc, elseFunc = null) => {
            return IfThen.when(!condition, thenFunc, elseFunc);
        },
        
        // If-else shorthand
        ifElse: (condition, truthyValue, falsyValue) => {
            return condition ? truthyValue : falsyValue;
        },
        
        // Chain multiple conditions
        chain: (...conditions) => {
            for (const condition of conditions) {
                if (Array.isArray(condition) && condition.length === 2) {
                    const [test, value] = condition;
                    if (test) return value;
                }
            }
            return null;
        },
        
        // Switch-like statement
        match: (value, cases, defaultCase = null) => {
            if (TypeChecker.isObject(cases) && cases[value] !== undefined) {
                const caseValue = cases[value];
                return TypeChecker.isFunction(caseValue) ? caseValue() : caseValue;
            }
            if (defaultCase !== null) {
                return TypeChecker.isFunction(defaultCase) ? defaultCase() : defaultCase;
            }
            return null;
        },
        
        // All conditions must be true
        all: (...conditions) => {
            return conditions.every(condition => {
                return TypeChecker.isFunction(condition) ? condition() : Boolean(condition);
            });
        },
        
        // At least one condition must be true
        any: (...conditions) => {
            return conditions.some(condition => {
                return TypeChecker.isFunction(condition) ? condition() : Boolean(condition);
            });
        },
        
        // None of the conditions should be true
        none: (...conditions) => {
            return !IfThen.any(...conditions);
        }
    };

    /**
     * ===========================================
     * VALIDATION UTILITIES
     * ===========================================
     */

    const Validator = {
        // Email validation
        isEmail: (value) => {
            if (!TypeChecker.isString(value)) return false;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(value);
        },
        
        // URL validation
        isURL: (value) => {
            if (!TypeChecker.isString(value)) return false;
            try {
                const url = new URL(value);
                return url.protocol === 'http:' || url.protocol === 'https:';
            } catch {
                return false;
            }
        },
        
        // Phone number validation (basic)
        isPhone: (value) => {
            if (!TypeChecker.isString(value)) return false;
            const phoneRegex = /^[\d\s\-\(\)\+]+$/;
            return phoneRegex.test(value.replace(/\s/g, ''));
        },
        
        // Password strength
        isStrongPassword: (value, minLength = 8) => {
            if (!TypeChecker.isString(value) || value.length < minLength) return false;
            return /[a-z]/.test(value) && /[A-Z]/.test(value) && /[0-9]/.test(value);
        },
        
        // Validate required fields
        isRequired: (value) => {
            return !TypeChecker.isEmpty(value);
        },
        
        // Validate length
        hasMinLength: (value, min) => {
            if (TypeChecker.isString(value)) return value.length >= min;
            if (TypeChecker.isArray(value)) return value.length >= min;
            return false;
        },
        
        hasMaxLength: (value, max) => {
            if (TypeChecker.isString(value)) return value.length <= max;
            if (TypeChecker.isArray(value)) return value.length <= max;
            return false;
        },
        
        // Range validation
        isInRange: (value, min, max) => {
            return ValueChecker.inRange(value, min, max);
        }
    };

    /**
     * ===========================================
     * DOM UTILITIES
     * ===========================================
     */

    const DOM = {
        // Safely query selector
        query: (selector, context = document) => {
            try {
                return context.querySelector(selector);
            } catch (error) {
                console.error('DOM query error:', error);
                return null;
            }
        },
        
        // Safely query all elements
        queryAll: (selector, context = document) => {
            try {
                return Array.from(context.querySelectorAll(selector));
            } catch (error) {
                console.error('DOM queryAll error:', error);
                return [];
            }
        },
        
        // Check if element exists
        exists: (selector) => {
            return DOM.query(selector) !== null;
        },
        
        // Wait for element
        waitFor: async (selector, timeout = 5000, context = document) => {
            const startTime = Date.now();
            const checkInterval = 100;
            
            while (Date.now() - startTime < timeout) {
                const element = DOM.query(selector, context);
                if (element) return element;
                await new Promise(resolve => setTimeout(resolve, checkInterval));
            }
            
            return null;
        },
        
        // Add event listener safely
        on: (element, event, handler, options = {}) => {
            if (TypeChecker.isElement(element) && TypeChecker.isFunction(handler)) {
                element.addEventListener(event, handler, options);
            }
        },
        
        // Remove event listener safely
        off: (element, event, handler, options = {}) => {
            if (TypeChecker.isElement(element) && TypeChecker.isFunction(handler)) {
                element.removeEventListener(event, handler, options);
            }
        },
        
        // Toggle class safely
        toggleClass: (element, className, force = undefined) => {
            if (TypeChecker.isElement(element)) {
                element.classList.toggle(className, force);
            }
        },
        
        // Add class safely
        addClass: (element, className) => {
            if (TypeChecker.isElement(element)) {
                element.classList.add(className);
            }
        },
        
        // Remove class safely
        removeClass: (element, className) => {
            if (TypeChecker.isElement(element)) {
                element.classList.remove(className);
            }
        },
        
        // Check if element has class
        hasClass: (element, className) => {
            return TypeChecker.isElement(element) && element.classList.contains(className);
        }
    };

    /**
     * ===========================================
     * ARRAY UTILITIES
     * ===========================================
     */

    const ArrayUtils = {
        // Find first item that matches condition
        findMatch: (array, condition) => {
            if (!TypeChecker.isArray(array)) return null;
            return Array.isArray(array) ? array.find(item => {
                return TypeChecker.isFunction(condition) 
                    ? condition(item) 
                    : Object.keys(condition).every(key => item[key] === condition[key]);
            }) : null;
        },
        
        // Remove duplicates
        unique: (array, key = null) => {
            if (!TypeChecker.isArray(array)) return [];
            if (key) {
                const seen = new Set();
                return array.filter(item => {
                    const value = SafeOperations.get(item, key);
                    if (seen.has(value)) return false;
                    seen.add(value);
                    return true;
                });
            }
            return [...new Set(array)];
        },
        
        // Group by key
        groupBy: (array, key) => {
            if (!TypeChecker.isArray(array)) return {};
            return array.reduce((groups, item) => {
                const groupKey = SafeOperations.get(item, key, 'unknown');
                if (!groups[groupKey]) groups[groupKey] = [];
                groups[groupKey].push(item);
                return groups;
            }, {});
        },
        
        // Sort by key
        sortBy: (array, key, order = 'asc') => {
            if (!TypeChecker.isArray(array)) return [];
            return [...array].sort((a, b) => {
                const aVal = SafeOperations.get(a, key);
                const bVal = SafeOperations.get(b, key);
                if (order === 'asc') {
                    return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
                } else {
                    return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
                }
            });
        },
        
        // Flatten nested arrays
        flatten: (array) => {
            if (!TypeChecker.isArray(array)) return [];
            return array.reduce((flat, item) => 
                flat.concat(Array.isArray(item) ? ArrayUtils.flatten(item) : item), 
                []
            );
        },
        
        // Chunk array into smaller arrays
        chunk: (array, size) => {
            if (!TypeChecker.isArray(array) || !TypeChecker.isInteger(size) || size <= 0) return [];
            const chunks = [];
            for (let i = 0; i < array.length; i += size) {
                chunks.push(array.slice(i, i + size));
            }
            return chunks;
        }
    };

    /**
     * ===========================================
     * OBJECT UTILITIES
     * ===========================================
     */

    const ObjectUtils = {
        // Merge objects deeply
        merge: (...objects) => {
            return objects.reduce((merged, obj) => {
                if (!TypeChecker.isObject(obj)) return merged;
                Object.keys(obj).forEach(key => {
                    if (TypeChecker.isObject(obj[key]) && TypeChecker.isObject(merged[key])) {
                        merged[key] = ObjectUtils.merge(merged[key], obj[key]);
                    } else {
                        merged[key] = obj[key];
                    }
                });
                return merged;
            }, {});
        },
        
        // Clone object
        clone: (obj) => {
            if (!TypeChecker.isObject(obj)) return obj;
            return JSON.parse(JSON.stringify(obj));
        },
        
        // Pick specific keys
        pick: (obj, keys) => {
            if (!TypeChecker.isObject(obj) || !TypeChecker.isArray(keys)) return {};
            return keys.reduce((picked, key) => {
                if (key in obj) picked[key] = obj[key];
                return picked;
            }, {});
        },
        
        // Omit specific keys
        omit: (obj, keys) => {
            if (!TypeChecker.isObject(obj) || !TypeChecker.isArray(keys)) return obj;
            const picked = {};
            Object.keys(obj).forEach(key => {
                if (!keys.includes(key)) picked[key] = obj[key];
            });
            return picked;
        },
        
        // Check if object has all specified keys
        hasKeys: (obj, keys) => {
            if (!TypeChecker.isObject(obj) || !TypeChecker.isArray(keys)) return false;
            return keys.every(key => key in obj);
        },
        
        // Check if object has any of the specified keys
        hasAnyKey: (obj, keys) => {
            if (!TypeChecker.isObject(obj) || !TypeChecker.isArray(keys)) return false;
            return keys.some(key => key in obj);
        }
    };

    /**
     * ===========================================
     * PROMISE UTILITIES
     * ===========================================
     */

    const PromiseUtils = {
        // Retry failed promise
        retry: async (func, maxAttempts = 3, delay = 1000) => {
            let lastError;
            for (let attempt = 1; attempt <= maxAttempts; attempt++) {
                try {
                    return await func();
                } catch (error) {
                    lastError = error;
                    if (attempt < maxAttempts) {
                        await new Promise(resolve => setTimeout(resolve, delay * attempt));
                    }
                }
            }
            throw lastError;
        },
        
        // Timeout wrapper
        timeout: (promise, timeoutMs) => {
            return Promise.race([
                promise,
                new Promise((_, reject) => 
                    setTimeout(() => reject(new Error('Operation timed out')), timeoutMs)
                )
            ]);
        },
        
        // Delay helper
        delay: (ms) => new Promise(resolve => setTimeout(resolve, ms)),
        
        // Promise.all with error handling for each
        allSettled: async (promises) => {
            return Promise.allSettled(promises);
        }
    };

    /**
     * ===========================================
     * STORAGE UTILITIES
     * ===========================================
     */

    const Storage = {
        // LocalStorage with JSON
        set: (key, value) => {
            if (TypeChecker.isString(key)) {
                try {
                    localStorage.setItem(key, JSON.stringify(value));
                    return true;
                } catch (error) {
                    console.error('Storage set error:', error);
                    return false;
                }
            }
            return false;
        },
        
        // LocalStorage get with JSON
        get: (key, defaultValue = null) => {
            if (!TypeChecker.isString(key)) return defaultValue;
            try {
                const item = localStorage.getItem(key);
                return item !== null ? JSON.parse(item) : defaultValue;
            } catch (error) {
                console.error('Storage get error:', error);
                return defaultValue;
            }
        },
        
        // LocalStorage remove
        remove: (key) => {
            if (TypeChecker.isString(key)) {
                try {
                    localStorage.removeItem(key);
                    return true;
                } catch (error) {
                    console.error('Storage remove error:', error);
                    return false;
                }
            }
            return false;
        },
        
        // LocalStorage clear
        clear: () => {
            try {
                localStorage.clear();
                return true;
            } catch (error) {
                console.error('Storage clear error:', error);
                return false;
            }
        },
        
        // SessionStorage wrapper
        session: {
            set: (key, value) => {
                if (TypeChecker.isString(key)) {
                    try {
                        sessionStorage.setItem(key, JSON.stringify(value));
                        return true;
                    } catch (error) {
                        console.error('SessionStorage set error:', error);
                        return false;
                    }
                }
                return false;
            },
            
            get: (key, defaultValue = null) => {
                if (!TypeChecker.isString(key)) return defaultValue;
                try {
                    const item = sessionStorage.getItem(key);
                    return item !== null ? JSON.parse(item) : defaultValue;
                } catch (error) {
                    console.error('SessionStorage get error:', error);
                    return defaultValue;
                }
            },
            
            remove: (key) => {
                if (TypeChecker.isString(key)) {
                    try {
                        sessionStorage.removeItem(key);
                        return true;
                    } catch (error) {
                        console.error('SessionStorage remove error:', error);
                        return false;
                    }
                }
                return false;
            }
        }
    };

    /**
     * ===========================================
     * DEBOUNCE & THROTTLE
     * ===========================================
     */

    const Timing = {
        // Debounce - delay execution until after delay
        debounce: (func, delay) => {
            let timeoutId;
            return function(...args) {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => func.apply(this, args), delay);
            };
        },
        
        // Throttle - limit execution frequency
        throttle: (func, delay) => {
            let lastExecution = 0;
            return function(...args) {
                const now = Date.now();
                if (now - lastExecution >= delay) {
                    lastExecution = now;
                    return func.apply(this, args);
                }
            };
        }
    };

    /**
     * ===========================================
     * EXPOSE TO GLOBAL SCOPE
     * ===========================================
     */

    // Export everything to window object
    window.IT = {
        type: TypeChecker,
        check: ValueChecker,
        safe: SafeOperations,
        if: IfThen,
        validate: Validator,
        dom: DOM,
        array: ArrayUtils,
        object: ObjectUtils,
        promise: PromiseUtils,
        storage: Storage,
        timing: Timing
    };

    // Also expose as __IF_THEN__ for compatibility
    window.__IF_THEN__ = window.IT;

    console.log('✓ if-then.js loaded successfully');

})();

