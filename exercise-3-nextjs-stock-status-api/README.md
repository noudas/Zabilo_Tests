# Exercise 3 – Next.js Stock Status Microservice

## Table of Contents

1. [Exercise Overview](#exercise-overview)
2. [Exercise Requirements](#exercise-requirements)
3. [Solution Overview](#solution-overview)
4. [Implementation Versions](#implementation-versions)

   * [Simple Version](#simple-version)
5. [Code Breakdown](#code-breakdown)

   * [Simple Version Breakdown](#simple-version-breakdown)
6. [Setup Instructions](#setup-instructions)
7. [API Testing & Usage](#api-testing--usage)
8. [Error Handling & Optimization](#error-handling--optimization)
9. [Scalability & Production Considerations](#scalability--production-considerations)

---

## Exercise Overview

**Exercise 3 – Hard: Create a Simple Next.js Microservice**

**Goal:** Assess your ability to build clean and scalable API endpoints using modern frameworks like Next.js.

**Task:**
Implement a mock API route that simulates inventory stock lookup by SKU.

---

## Exercise Requirements

### Core Requirements:

* API route available at: `/api/stock-status?sku=XXXXX`
* Returns JSON in the following format:

  ```json
  {
    "sku": "XXXXX",
    "stock": 17,
    "status": "low"
  }
  ```
* Rules:

  * If `stock < 20` → `"status": "low"`
  * Else → `"status": "ok"`

### Technical Notes:

* Stock should be simulated via a hardcoded or mock function
* Input should be validated
* No database or external dependencies required

---

## Solution Overview

This solution uses a **Next.js API Route** to implement a lightweight microservice that:

* Accepts a `sku` via query string
* Simulates a stock level using a deterministic function
* Responds with a clean JSON structure including the status logic

It is intended for demonstration purposes, and can be extended into a production-ready microservice.

---

## Implementation Versions

### Simple Version

**File:** `/pages/api/stock-status.ts`

**Key Traits:**

* Single-file implementation
* Simulates stock using a hash function
* Stateless, dependency-free
* Basic validation included

---

## Code Breakdown

### Simple Version Breakdown

#### File: `pages/api/stock-status.ts`

```ts
import { NextApiRequest, NextApiResponse } from 'next';

function getMockStock(sku: string): number {
  let hash = 0;
  for (let i = 0; i < sku.length; i++) {
    hash += sku.charCodeAt(i);
  }
  return hash % 50; // Stock between 0 and 49
}

export default function handler(req: NextApiRequest, res: NextApiResponse) {
  const { sku } = req.query;

  if (!sku || typeof sku !== 'string') {
    return res.status(400).json({ error: 'SKU is required and must be a string.' });
  }

  const stock = getMockStock(sku);
  const status = stock < 20 ? 'low' : 'ok';

  res.status(200).json({ sku, stock, status });
}
```

---

## Setup Instructions

This exercise assumes you're working within an existing **Next.js project**.

### Steps:

1. Inside your project, create the following file:

```
/pages/api/stock-status.ts
```

2. Paste in the code from the **Simple Version** above.

3. (Optional) If there was a Backend, test it in a browser or tool like Postman:

   * Start your project’s dev server:

     ```bash
     npm run dev
     ```
   * Visit:

     ```
     http://localhost:3000/api/stock-status?sku=ABC123
     ```

> ⚠️ No additional setup or installation is required. This is a front-end-only mock API route.

---

## API Testing & Usage

### ✅ Valid Request

```
GET /api/stock-status?sku=ABC123
```

**Example Response:**

```json
{
  "sku": "ABC123",
  "stock": 17,
  "status": "low"
}
```

---

### ❌ Invalid Request (No SKU)

```
GET /api/stock-status
```

**Response:**

```json
{
  "error": "SKU is required and must be a string."
}
```

---

## Error Handling & Optimization

### Current Handling:

* Ensures the `sku` query parameter exists and is a string
* Returns appropriate HTTP 400 response on invalid input

### Optimizations:

* Uses deterministic, stateless mock function (lightweight and predictable)
* Requires no external calls or dependencies

---

## Scalability & Production Considerations

If turning this into a production-ready microservice, consider:

| Feature            | Recommendation                                                  |
| ------------------ | --------------------------------------------------------------- |
| **Stock Data**     | Connect to a real-time inventory database or API                |
| **Validation**     | Use libraries like `zod` or `yup` for strict validation         |
| **Authentication** | Protect endpoint with an API key or JWT                         |
| **Rate Limiting**  | Use middleware or an edge gateway to throttle abuse             |
| **Monitoring**     | Add logging with Sentry or Datadog                              |
| **Deployment**     | Use Vercel (ideal for Next.js) or host as a serverless function |
| **Caching**        | Use CDN or Redis for high-demand SKUs                           |

---