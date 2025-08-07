# PrestaShop Module Exercise - File Structure

## Overview
This exercise provides **two implementation versions** for the same PrestaShop module customization task.

## Directory Structure

```
exercise-1-prestashop-editable-message/
├── README.md                    # Main documentation
├── STRUCTURE.md                 # This file
├── simple-version/              # Minimal implementation
│   ├── mymodule.php            # Main module (simplified)
│   └── display.tpl             # Frontend template
└── complete-version/            # Full-featured implementation
    ├── mymodule.php            # Main module (complete)
    ├── config.xml              # Module configuration
    ├── logo.png                # Module icon
    ├── index.php               # Security redirect
    ├── LICENSE                 # License file
    ├── install/
    │   ├── index.php           # Security redirect
    │   ├── install.sql         # Database setup
    │   └── uninstall.sql       # Database cleanup
    ├── translations/
    │   ├── index.php           # Security redirect
    │   └── en.php              # English translations
    └── views/
        ├── index.php           # Security redirect
        ├── css/
        │   ├── index.php       # Security redirect
        │   └── mymodule.css    # Frontend styles
        └── templates/
            ├── index.php       # Security redirect
            ├── admin/
            │   ├── index.php   # Security redirect
            │   └── configure.tpl # Admin template
            └── hook/
                ├── index.php   # Security redirect
                └── display.tpl # Frontend template
```

## Version Comparison

| Feature | Simple Version | Complete Version |
|---------|---------------|-----------------|
| **Files** | 2 files | 15+ files |
| **Languages** | Single | Multilingual |
| **Styling** | Basic | Professional CSS |
| **Security** | Basic | Full measures |
| **Database** | Configuration only | SQL files |
| **Translations** | None | Full support |
| **Complexity** | Low | High |
| **Use Case** | Learning | Production |

## Quick Start

### For Learning:
1. Use `simple-version/` folder
2. Copy `mymodule.php` and `display.tpl`
3. Install in PrestaShop

### For Production:
1. Use `complete-version/` folder
2. Copy entire folder structure
3. Customize as needed
4. Install in PrestaShop
