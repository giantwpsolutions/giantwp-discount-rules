# Smart Engine

**Smart Engine is a Pro feature.** You need the GiantWP Discount Rules Pro add-on to use it.

---

## What does Smart Engine do?

Think of Smart Engine as a tireless sales assistant who stands at the checkout counter 24 hours a day, 7 days a week — and personally talks to every single customer who puts something in their cart.

The moment a customer visits your cart page, Smart Engine looks at what they have in their cart, who they are, and whether they are about to leave — and then shows them a short, personalised message designed to get them to complete their purchase.

Every customer gets a different message based on their situation. A first-time shopper gets a warm welcome. Someone who is one item away from a bulk discount gets a nudge. A customer who is about to leave gets an urgent "wait — here's a deal" offer. All of this happens automatically, without you doing anything.

---

## What does it look like?

A small notification card appears in the **bottom-left corner** of the cart page. It slides in smoothly after the customer has had a moment to look at their cart, stays for about 7 seconds, and then quietly fades away. The customer can also close it at any time.

The message is written by AI — it sounds natural and human, not like a generic popup. It speaks directly to what the customer has in their cart right now.

---

## What does Smart Engine actually offer customers?

Smart Engine always picks the most relevant tactic for each customer. Here is what it can do:

### Free Shipping Nudge
**When:** The customer's cart total is under $100.
**What happens:** No discount is given. Instead, the AI tells the customer how much more they need to spend to unlock free shipping. For example: *"You're only $18 away from free shipping — add one more item and ship for free!"*

### Bulk Discount
**When:** The customer has 10 or more of the same product in their cart.
**What happens:** A 10% discount is automatically applied to their order. The AI congratulates them and encourages them to check out.

### Bulk Nudge
**When:** The customer has exactly 9 of the same product.
**What happens:** No discount yet. The AI tells them they are just one item away from unlocking the 10% bulk discount.

### BOGO Nudge
**When:** The customer has 2 or more different products in their cart.
**What happens:** The AI pitches the deal — add one more product and the cheapest item in the cart becomes free.

### Exit Rescue
**When:** The customer is about to leave the cart page (they move their mouse toward the top of the screen to close the tab, or press back on mobile).
**What happens:** This is Smart Engine's most powerful move. An instant discount (the percentage you set) is applied to their cart, and the AI delivers an urgent, personalised message to stop them from leaving. The offer appears before they are gone.

### New Customer Welcome
**When:** It is the customer's first time buying from your store.
**What happens:** No discount. The AI gives them a warm, encouraging welcome and invites them to complete their first order.

### Loyal Customer Recognition
**When:** A returning or long-time customer.
**What happens:** No discount. The AI thanks them genuinely for their loyalty and encourages them to check out.

---

## Setting it up

### 1. Turn Smart Engine on

Go to **WooCommerce → GiantWP Discount Rules → Smart Engine** in your WordPress dashboard. Flip the toggle to **On**.

### 2. Connect an AI provider

Smart Engine needs to connect to an AI service to write the personalised messages. You have three options:

| Provider | Best for |
|----------|----------|
| **Anthropic Claude** | Best balance of speed and quality. Recommended for most stores. |
| **OpenAI GPT-4o** | Highest accuracy. Good if you already have an OpenAI account. |
| **Google Gemini** | Competitive pricing. Good alternative. |

You only need to connect **one**. They all do the same job here.

**To connect:**
1. Click on the provider you want to use.
2. Go to the provider's website and create a free account if you do not have one yet.
3. Generate an API key on their platform.
4. Paste the key into the API key field and click **Connect & Save**.

You will see a green "Connected" badge when it works. Your key is saved securely — it is encrypted in your database and never visible to anyone.

**Where to get your API key:**

- Anthropic Claude → [console.anthropic.com](https://console.anthropic.com/)
- OpenAI → [platform.openai.com/api-keys](https://platform.openai.com/api-keys)
- Google Gemini → [aistudio.google.com/app/apikey](https://aistudio.google.com/app/apikey)

> All three providers offer free credits when you sign up. For most small-to-medium stores, the monthly AI cost for Smart Engine is under $1.

### 3. Set your Exit Rescue Discount

This is the only number you need to decide. It is the discount percentage Smart Engine offers to customers who are about to abandon their cart.

- The default is **15%**.
- You can set it anywhere from **5% to 50%**.
- Think of it as your "last resort" offer — what is the maximum you are willing to give away to save a sale?

### 4. Add Store Notes (optional but recommended)

This is where you tell the AI about your store so its messages feel on-brand.

You can write anything — your brand tone, what makes your store special, current promotions, things to always mention. For example:

> *"We sell handmade candles. Our tone is warm and cosy. Always mention that our candles are made with natural soy wax and burn for 60+ hours."*

> *"We are a fitness supplement store. Be energetic and motivating. Mention our free shaker with orders over $60."*

> *"This is a B2B wholesale store. Be professional and concise. Mention our bulk pricing tiers."*

If you leave it empty, Smart Engine will still work — it just writes more generic messages.

### 5. Save

Click **Save Settings**. Smart Engine is now live on your store.

---

## Does it work even if the AI is slow or unavailable?

Yes. Smart Engine always shows a message — even if the AI cannot respond in time.

When a customer loads the cart page, a sensible default message is shown immediately (for example, "Add a little more to unlock free shipping!"). If the AI responds quickly, the message is smoothly updated with the personalised version. The customer never sees a blank or broken notification.

---

## Will it hurt my profit margins?

Not if you use Margin Protection Guard. If you have Margin Guard enabled in your Settings, Smart Engine will always respect it:

- It will never apply a discount that sells a product below its cost price.
- It respects your global minimum margin percentage.
- It respects your global maximum discount cap.

So even if your Exit Rescue Discount is set to 20%, if a product can only sustain 8% off before going below cost, Smart Engine will apply 8% — not 20%.

If you have not set up Margin Guard yet, go to **Settings → Margin Protection Guard** and enter your product cost prices.

---

## How much does the AI cost to run?

Very little. Smart Engine is designed to be token-efficient:

- The AI only writes the **message** (~60 words). It does not make the discount decision — that is done instantly without any AI.
- Each customer visit uses roughly **80 tokens**. At Anthropic Claude Haiku pricing, that is less than $0.001 per customer.
- The message is **cached per visit** — if the same customer refreshes the cart, the AI is not called again unless their cart situation changes.

For a store with 500 cart visits per month, the AI cost is typically **under $0.50/month**.

---

## Stats

The Smart Engine page shows three numbers at the top:

| Stat | What it means |
|------|---------------|
| **Customers engaged** | How many cart visits Smart Engine has run on |
| **Offers applied** | How many of those got an actual discount (exit rescue or bulk) |
| **Extra revenue** | Total value of orders where a Smart Engine discount was active |

---

## Frequently Asked Questions

**Does Smart Engine interfere with my other discount rules?**
No. It runs completely separately. Your existing rules — BOGO, flat/percentage, bulk pricing — all continue to work as normal. Smart Engine simply adds a layer on top.

**Can I change the $100 free shipping threshold or the 10-item bulk requirement?**
These are fixed by design to keep things simple and working out of the box. If your store needs different values, please contact support.

**Will my customers know it is AI?**
No. The AI is instructed never to mention that it is AI. Messages are written to sound like they come from your store.

**What if a customer keeps refreshing to trigger the exit rescue discount?**
Smart Engine clears the exit intent signal as soon as the rescue offer is shown. The customer cannot repeatedly trigger it — they would have to leave and come back to trigger it again.

**Does it work on mobile?**
Yes. Exit intent on mobile is detected when the customer presses the back button or switches away from the tab.

**Does it work with the new WooCommerce block-based cart?**
Yes. Smart Engine is fully compatible with both the classic WooCommerce cart and the newer block-based cart.

**Do customers have to be logged in?**
No. Smart Engine works for guest shoppers too.

---

## Privacy & Data

Smart Engine sends a small amount of anonymised information to your chosen AI provider to generate the message. Here is exactly what is sent:

**Sent to the AI:**
- Whether the customer is new, returning, or a loyal buyer
- How many past orders they have placed
- Their cart total
- How many items are in their cart
- Whether they triggered the exit intent signal
- Your Store Notes (if you entered any)

**Never sent:**
- Customer name, email address, or any contact details
- Billing or shipping address
- Payment information
- Individual product names or prices
- Anything that could identify the customer

Your chosen provider processes this data to generate the message and does not retain it. Full legal links:

| Provider | Terms of Service | Privacy Policy |
|----------|-----------------|----------------|
| Anthropic | [View](https://www.anthropic.com/legal/consumer-terms) | [View](https://www.anthropic.com/legal/privacy) |
| OpenAI | [View](https://openai.com/policies/terms-of-use) | [View](https://openai.com/policies/privacy-policy) |
| Google | [View](https://ai.google.dev/gemini-api/terms) | [View](https://policies.google.com/privacy) |
