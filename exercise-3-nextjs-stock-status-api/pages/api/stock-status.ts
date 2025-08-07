import { NextApiRequest, NextApiResponse } from 'next';

// Simulated stock value function
function getMockStock(sku: string): number {
  // Simulate different stock levels based on SKU hash
  let hash = 0;
  for (let i = 0; i < sku.length; i++) {
    hash += sku.charCodeAt(i);
  }
  return (hash % 50); // returns stock between 0 and 49
}

export default function handler(req: NextApiRequest, res: NextApiResponse) {
  const { sku } = req.query;

  if (!sku || typeof sku !== 'string') {
    return res.status(400).json({ error: 'SKU is required and must be a string.' });
  }

  const stock = getMockStock(sku);
  const status = stock < 20 ? 'low' : 'ok';

  return res.status(200).json({
    sku,
    stock,
    status
  });
}
