# PrestaShop Module Customization – Exercise 1

## 📝 Exercise 1 – Easy: Modify a PrestaShop Module

**Goal:**  
Evaluate your understanding of PrestaShop module customization.

### ❓ Task:

A PrestaShop module currently displays the following message on the homepage:

> **"Enjoy our summer sales!"**

You need to make this message **editable** from the module’s **back office**.

### ✅ What to do:

- Provide code or pseudo-code (you don’t need a working environment).  
- Explain which files you would modify (`.tpl`, `.php`, `install()` method, etc.).  
- Describe how to make this message **translatable** and **editable** from the admin interface.

---

## ✅ Solution & Explanation

We need to modify a PrestaShop module so that a hardcoded message becomes:

- **Editable from the admin panel**
- **Translatable in multiple languages**
- **Displayed on the homepage**

---

## 1. Modify the Module's Main PHP File (`mymodule.php`)

This file contains the module's installation logic, configuration form, and display hook.

### a. Add a Default Config Value

Inside the `install()` method, set a default message:

```php
Configuration::updateValue('MYMODULE_MESSAGE', $this->l('Enjoy our summer sales!'));
```
This stores the message in PrestaShop's configuration table and makes it translatable by wrapping it with `$this->l()`.

---
### b. Add a Configuration Form
In the `getContent()` method, display and handle the form:

```php
if (Tools::isSubmit('submit_mymodule')) {
    foreach (Language::getLanguages(false) as $lang) {
        $msg = Tools::getValue('MYMODULE_MESSAGE_' . $lang['id_lang']);
        Configuration::updateValue('MYMODULE_MESSAGE', $msg, false, null, $lang['id_lang']);
    }
}
```
Then, generate the form with multilingual support using `renderForm()`:

```php
[
  'type' => 'text',
  'label' => $this->l('Homepage Message'),
  'name' => 'MYMODULE_MESSAGE',
  'lang' => true, // Enables multilingual input fields
  'required' => true
]
```
This will generate one input field per language in the back office form.

---

### c. Display the Message on the Homepage
Register to the displayHome hook and output the message:

```php
$this->context->smarty->assign([
    'mymodule_message' => Configuration::get('MYMODULE_MESSAGE', $this->context->language->id),
]);
return $this->display(__FILE__, 'views/templates/hook/display.tpl');
```
This ensures the message shown is based on the current frontend language.

---

## 2. Create the Template `display.tpl`

Inside: views/templates/hook/display.tpl
```tpl
<div class="mymodule-message">
    {$mymodule_message|escape:'html':'UTF-8'}
</div>
```
This will safely render the configured message on the homepage.

---

## 3. Files You Modify or Add
| File                               | Purpose                                                 |
| ---------------------------------- | ------------------------------------------------------- |
| `mymodule.php`                     | Handles form creation, config storage, and display hook |
| `views/templates/hook/display.tpl` | Renders the message in the frontend                     |

---

## 4. Making It Translatable

To support multiple languages:

Use `'lang' => true` in the form field

Use `Configuration::get(..., id_lang)` and `Configuration::updateValue(..., ..., ..., ..., id_lang)` to store and retrieve values per language

Use `$this->l()` to make default values and labels translatable

---

### Admins can now:

- Edit the message from the module settings
- Enter a version per language
- See the result immediately on the front page

---

Recommended Structure

```bash
mymodule/
├── mymodule.php
└── views/
    └── templates/
        └── hook/
            └── display.tpl
```

---