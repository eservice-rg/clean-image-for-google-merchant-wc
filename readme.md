# Clean Image for Google Merchant (WC)

**Contributors:** merchantpure  
**Tags:** woocommerce, google merchant, feed image, product image, ai  
**Requires at least:** WordPress 5.0  
**Tested up to:** WordPress 6.9  
**Requires PHP:** 7.4  
**Stable tag:** 1.3.1  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

Adds a dedicated, policy-compliant product image field to WooCommerce products, designed for Google Merchant Center feeds.

---

## Description

If you are using WooCommerce and your website displays promotional product images — banners, text overlays, badges, stickers — your feed for **Google Merchant Center**, **Facebook / Meta Shops**, **Pinterest**, or **TikTok Shop** can be rejected instantly.

Google strictly requires **clean, neutral, non-promotional main images**.  
But merchants often refuse to give up their beautiful promotional product graphics used on the website.

**Clean Image for Google Merchant (WC) solves this problem elegantly.**  
You can keep using promotional images on your website while assigning *clean and policy-safe images* exclusively to product feeds.

The free version allows you to upload separate feed images for Google.  
The **PRO version** adds full AI automation and multi-channel feed image management.

---

## Why this plugin exists

Merchants often say:

- “My feed keeps getting rejected because product images have text.”
- “Google doesn’t want promotional overlays but I can’t remove them from my website.”
- “I sell more with promotional images but Google Merchant blocks my products.”

Now you don’t have to choose.

Your website keeps its marketing visuals.  
Your feeds stay compliant and approved.

---

## Real-world results

In real-world usage, this plugin has helped WooCommerce merchants:

- Restore fully disapproved Google Merchant feeds in a few days
- Keep promotional images on their storefront
- Resume Google Ads campaigns
- Recover lost traffic and orders without rebuilding product images

This plugin was built to solve a **real recurring problem**, not a theoretical one.

---

## Key Features

### ✅ Free Version

- Dedicated feed image for **Google Merchant Center**
- Website promotional images remain untouched
- Fully policy-compliant feeds
- Compatible with any WooCommerce store
- Lightweight and fast

---

### ⭐ PRO Version (Upgrade Recommended)

Includes all free features **plus**:

#### AI Clean Feed Image Generator
Generates **1024×1024 transparent PNG** images optimized for product feeds.  
Removes backgrounds, text overlays, banners and promotional elements.

#### OpenAI API Key Integration
Generate clean images directly inside WooCommerce.

#### Global Default Images (Per Channel)
Fallback images for Google, Facebook, Pinterest and TikTok.

#### Feed Image Bulk Editor
Bulk upload, generate, reset and manage feed images for all products.

#### Multi-Channel CSV Export
Export product IDs, titles and all feed image meta fields.

#### Screen Options Integration
Enable or disable PRO metaboxes using native WordPress Screen Options.

#### In-Product AI Button
One-click **Generate Clean Image (AI)** directly inside the product edit page.

#### Additional Channel Metaboxes
Separate feed images for:
- Facebook / Meta
- Pinterest
- TikTok

**Upgrade to PRO:**  
👉 https://davidepuzzo.cloud/6k6o9

---

## Installation

1. Upload the plugin ZIP via **Plugins → Add New → Upload Plugin**
2. Install and activate
3. Ensure WooCommerce is installed and active
4. Edit any product
5. Use the **Google Merchant – Clean Image** metabox to assign a feed-safe image

---

## Screenshots

1. Google Merchant Center feed image metabox  
2. WooCommerce Screen Options panel  
3. OpenAI API & Global Defaults (PRO)  
4. Feed Image Bulk Editor (PRO)  
5. Multi-Channel CSV Export (PRO)  
6. PRO metabox toggles via Screen Options  
7. AI Clean Image button comparison (PRO)  
8. Facebook, Pinterest & TikTok feed metaboxes (PRO)

---

## Frequently Asked Questions

### Does this plugin require WooCommerce?
Yes. WooCommerce must be installed and active.

### Does the plugin modify my original images?
No. Original product images are never modified or replaced.

### What is a “clean image”?
A product image without logos, watermarks, discount badges or promotional text.

### What happens if I don’t set a clean image?
The plugin falls back to the product featured image.

### Which meta keys does the free version use?
- `_gm_image_id`
- `_gm_image_url`

### Is it compatible with feed managers?
Yes. Any feed plugin supporting custom meta fields can read `_gm_image_url`.

### Does the plugin affect frontend output?
No. It only affects admin data and feed exports.

---

## PRO Meta Keys

- **Meta / Facebook:** `_gm_image_id_meta`, `_gm_image_url_meta`
- **Pinterest:** `_gm_image_id_pinterest`, `_gm_image_url_pinterest`
- **TikTok:** `_gm_image_id_tiktok`, `_gm_image_url_tiktok`

Developers can extend channels via the `wc_gm_image_pro_channels` filter.

---

## AI Pricing Explained

- Cost depends **only on the generated 1024×1024 image**
- Original upload size does not matter
- Typical cost: **$0.02 – $0.06 per image**
- Uses your own OpenAI API Key

AI usage is **optional**.  
Manual image upload works exactly the same.

---

## Changelog

### 1.3.1
- Stable version

### 1.3
- Bug fix

### 1.2.3
- Added PRO version notice
- Improved Google Merchant compliance guidance
- Bug fix

### 1.2.2
- Restored original metabox logic
- Fixed missing metabox edge cases
- English localization

### 1.2.1
- Improved JS/CSS loading
- Added WooCommerce dependency declaration

### 1.2.0
- Initial release

## WordPress.org
Plugin page:  
https://wordpress.org/plugins/clean-image-for-google-merchant-wc/

## Author
MerchantPure

## License
GPL v2 or later
