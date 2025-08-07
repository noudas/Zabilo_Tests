# Zabilo Tests

# 🛠️ Full Stack Developer Technical Test – E-commerce Profile

This repository contains my solutions to a 4-part technical test designed to assess full stack development skills in the context of an e-commerce environment.

---

## 🚀 Objectives

This test focuses on the ability to:

- Analyze and understand technical requirements
- Propose clean, maintainable, and realistic solutions
- Write clear and relevant code snippets or pseudo-code
- Explain technical and architectural decisions
- Handle edge cases, limitations, and possible improvements

> 🧠 Each exercise includes both code and written reasoning to provide context around decisions, alternatives, and potential optimizations.

---

## 📋 Exercises Overview

### ✅ **Exercise 1 – PrestaShop Module Customization (Easy)**

**Goal:** Make a static homepage message editable and translatable via the module's back office.

- Approach: Describe changes to `.tpl`, `.php`, and `install()` methods
- Explanation: How to register back office fields and handle translation
- Output: Code or pseudo-code with architecture explanation

---

### ✅ **Exercise 2 – Google Apps Script Automation (Medium)**

**Goal:** Automate daily sorting of orders from Google Sheets based on status.

- Approach: Write a script that runs daily
- Logic: Move "Pending" orders from one sheet to another
- Additional: Setup trigger, error handling, and optimization

---

### ✅ **Exercise 3 – Next.js Stock Status API (Hard)**

**Goal:** Build a microservice API in Next.js to return stock status based on SKU.

- Endpoint: `/api/stock-status?sku=XXXXX`
- Output: JSON with SKU, stock value, and status ("low" or "ok")
- Design: Clean API route with mock data and explanation of scalable architecture

---

### ✅ **Exercise 4 – PrestaShop → External API Sync (Very Hard)**

**Goal:** Send new PrestaShop products to an external API automatically.

- Trigger: Hook into product creation
- Payload: JSON with product data (ID, name, price, stock)
- Architecture: Describe services, error handling, retries, and security measures
- Optional: Scalability suggestions for high-volume product syncs

---

## 🧩 Tech & Concepts Covered

- PrestaShop module architecture and hooks
- Google Apps Script automation and triggers
- REST API design using Next.js
- System architecture, error handling, and scalability
- Clean code and thoughtful decision making

---

## 📂 Structure
```
/exercise-1-prestashop-editable-message
/exercise-2-google-apps-script-orders
/exercise-3-nextjs-stock-status-api
/exercise-4-prestashop-sync-external-api
```

Each folder contains:
- `README.md` with problem analysis and reasoning
- `code.js`, `api.js`, or equivalent script/code file with the solution

---

## 📌 Notes

- PrestaShop installation is not required — solutions are based on pseudo-code and standard module structure.
- Focus is placed on **reasoning**, **architecture**, and **clean code**, not just working output.
