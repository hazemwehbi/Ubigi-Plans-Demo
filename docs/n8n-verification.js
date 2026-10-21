// Exemple Node.js côté serveur. Adapter les entrées et sorties à la version
// des nœuds Webhook/Code n8n. Activer le corps brut et autoriser crypto si nécessaire.
// Ne jamais publier le secret dans un workflow versionné sur un dépôt public.
const { createHmac, timingSafeEqual } = require('node:crypto');
function verify(rawBody, receivedHex, secret) {
 if (!Buffer.isBuffer(rawBody) || typeof receivedHex !== 'string' || !/^[a-f0-9]{64}$/.test(receivedHex) || !secret) return false;
 const expected = createHmac('sha256', secret).update(rawBody).digest();
 return timingSafeEqual(expected, Buffer.from(receivedHex, 'hex'));
}
module.exports = { verify };
