# PrestaShop Module Customization – Exercise 1

## Table of Contents

- [Overview](#overview)
- [Exercise Goal](#exercise-goal)
- [Task Description](#task-description)
- [Requirements](#requirements)
- [Solution](#solution)
  - [1. Module PHP File Modifications](#1-module-php-file-modifications)
  - [2. Template Creation](#2-template-creation)
  - [3. File Structure](#3-file-structure)
  - [4. Multilingual Support](#4-multilingual-support)
- [Implementation Steps](#implementation-steps)
- [Final Result](#final-result)

---

## Overview

This exercise focuses on **PrestaShop module customization** by transforming a static message into a dynamic, admin-editable component.

---

## Exercise Goal

**Objective:** Evaluate understanding of PrestaShop module customization principles and implementation.

---

## Task Description

### Current State
A PrestaShop module displays a **hardcoded message** on the homepage:

> **"Enjoy our summer sales!"**

### Required Transformation
Make this message **editable** from the module's **back office** interface.

---

## Requirements

### What You Need to Deliver:

1. **Code or pseudo-code** (no working environment required)
2. **File modification explanations** (`.tpl`, `.php`, `install()` method, etc.)
3. **Multilingual implementation** description
4. **Admin interface integration** details

---

## Solution

### Overview
Transform the hardcoded message into a **configurable, multilingual component** that is:

- ✅ **Editable from the admin panel**
- ✅ **Translatable in multiple languages**
- ✅ **Displayed on the homepage**

---

### 1. Module PHP File Modifications

#### File: `mymodule.php`

This file handles the module's core functionality including installation, configuration, and display logic.

#### a. Default Configuration Setup

**Location:** `install()` method

```php
Configuration::updateValue('MYMODULE_MESSAGE', $this->l('Enjoy our summer sales!'));
```

**Purpose:** 
- Stores the default message in PrestaShop's configuration table
- Makes it translatable using `$this->l()` wrapper

#### b. Admin Configuration Form

**Location:** `getContent()` method

**Form Processing:**
```php
if (Tools::isSubmit('submit_mymodule')) {
    foreach (Language::getLanguages(false) as $lang) {
        $msg = Tools::getValue('MYMODULE_MESSAGE_' . $lang['id_lang']);
        Configuration::updateValue('MYMODULE_MESSAGE', $msg, false, null, $lang['id_lang']);
    }
}
```

**Form Field Configuration:**
```php
[
    'type' => 'text',
    'label' => $this->l('Homepage Message'),
    'name' => 'MYMODULE_MESSAGE',
    'lang' => true, // Enables multilingual input fields
    'required' => true
]
```

**Result:** Generates one input field per language in the back office.

#### c. Frontend Display Logic

**Location:** Hook method (e.g., `hookDisplayHome`)

```php
$this->context->smarty->assign([
    'mymodule_message' => Configuration::get('MYMODULE_MESSAGE', $this->context->language->id),
]);
return $this->display(__FILE__, 'views/templates/hook/display.tpl');
```

**Purpose:** Ensures the message displays based on the current frontend language.

---

### 2. Template Creation

#### File: `views/templates/hook/display.tpl`

```tpl
<div class="mymodule-message">
    {$mymodule_message|escape:'html':'UTF-8'}
</div>
```

**Features:**
- Safely renders the configured message
- HTML escaping for security
- UTF-8 encoding support

---

### 3. File Structure

| File | Purpose | Modification Type |
|------|---------|------------------|
| `mymodule.php` | Main module logic | **Modify** |
| `views/templates/hook/display.tpl` | Frontend template | **Create** |

---

### 4. Multilingual Support

#### Implementation Methods:

1. **Form Configuration:**
   - Use `'lang' => true` in form fields
   - Generates language-specific input fields

2. **Data Storage:**
   - `Configuration::get(..., id_lang)` - Retrieve per language
   - `Configuration::updateValue(..., ..., ..., ..., id_lang)` - Store per language

3. **Translation Support:**
   - `$this->l()` - Makes default values and labels translatable

---

## Implementation Steps

### Step 1: Modify `mymodule.php`
1. Add default configuration in `install()` method
2. Create configuration form in `getContent()` method
3. Add display hook method

### Step 2: Create Template
1. Create `views/templates/hook/` directory
2. Add `display.tpl` file with message rendering

### Step 3: Test
1. Install/upgrade the module
2. Configure message in admin panel
3. Verify display on homepage

---

## Final Result

### Admin Capabilities:
- ✅ **Edit message** from module settings
- ✅ **Enter versions** per language
- ✅ **See results** immediately on frontend

### Recommended Module Structure:
```
mymodule/
├── mymodule.php
└── views/
    └── templates/
        └── hook/
            └── display.tpl
```

---

## Additional Notes

- **No working environment required** - pseudo-code is acceptable
- **Focus on PrestaShop best practices** for configuration management
- **Consider security** with proper escaping in templates
- **Test multilingual functionality** thoroughly

---

*This exercise demonstrates core PrestaShop module development concepts including configuration management, multilingual support, and admin interface integration.*