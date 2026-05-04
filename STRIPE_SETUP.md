# Stripe Integration Setup Guide

## Problem Fixed
The embroidery converter app now properly supports Stripe checkout for Pro and Business plans.

### What Changed
1. **Plan Model** - Already supports `stripe_monthly_price_id` and `stripe_yearly_price_id` fields
2. **Seeder** - Now explicitly sets these fields to `null` (to be configured via admin panel)
3. **PlanController** - Enhanced with better error handling and logging:
   - Shows clear error messages to users when Stripe is misconfigured
   - Shows detailed admin-only messages for debugging
   - Better exception handling for Stripe API failures
4. **Pricing View** - Shows admin warnings when:
   - Stripe credentials are missing
   - Plan Stripe price IDs are not configured
5. **User Model** - Improved `activePlan()` method:
   - Better fallback handling when subscription exists but plan is not found
   - Prevents null pointer exceptions

## Quick Start (Development)

### Step 1: Set Environment Variables

Edit `.env`:
```env
STRIPE_KEY=pk_test_51234567890abcdefghijklmno  # Get from https://dashboard.stripe.com/apikeys
STRIPE_SECRET=sk_test_abcdefghijklmno1234567890  # Use TEST keys for development
STRIPE_WEBHOOK_SECRET=whsec_test_xxxxxxxxxxxx    # Get from https://dashboard.stripe.com/webhooks
```

Get these values:
1. Go to https://dashboard.stripe.com/apikeys
2. Copy the **Test** keys (they start with `pk_test_` and `sk_test_`)
3. For webhooks, go to https://dashboard.stripe.com/webhooks (needed only for production)

### Step 2: Create Stripe Products & Prices

In Stripe Dashboard (https://dashboard.stripe.com/products):

#### Pro Plan
1. Create product: "Embroidery Converter Pro"
2. Add pricing:
   - Monthly: $9.99 (billing period: monthly) → Copy Price ID (e.g., `price_1234567890abcdef`)
   - Yearly: $99.00 (billing period: yearly) → Copy Price ID
3. Note down both Price IDs

#### Business Plan
1. Create product: "Embroidery Converter Business"
2. Add pricing:
   - Monthly: $29.99 (billing period: monthly) → Copy Price ID
   - Yearly: $299.00 (billing period: yearly) → Copy Price ID
3. Note down both Price IDs

### Step 3: Configure Plans in Admin Panel

1. Log in as admin to your app
2. Go to Admin → Plans
3. Click Edit for "Pro" plan:
   - Stripe Monthly Price ID: `price_xxxxxxxxxxxx` (paste from step 2)
   - Stripe Yearly Price ID: `price_xxxxxxxxxxxx` (paste from step 2)
   - Click Update
4. Click Edit for "Business" plan:
   - Stripe Monthly Price ID: `price_xxxxxxxxxxxx`
   - Stripe Yearly Price ID: `price_xxxxxxxxxxxx`
   - Click Update

### Step 4: Test the Flow

1. Go to /pricing in your app
2. As admin, verify no red/yellow warnings appear
3. Log in as regular user
4. Click "Get Pro" or "Get Business"
5. Use test card: `4242 4242 4242 4242`
6. Expiry: Any future date (e.g., 12/25)
7. CVC: Any 3 digits (e.g., 123)
8. Click "Subscribe"
9. Should redirect to success page

## Database Impact

### Existing Tables
- **plans** - Already has `stripe_monthly_price_id` and `stripe_yearly_price_id` columns (nullable)
- **subscriptions** - Created by Cashier, tracks active subscriptions
- **subscription_items** - Created by Cashier, tracks subscription line items

### No Migration Needed
The database schema already supports Stripe integration. Just update seeders if you haven't run them.

## Testing Scenarios

### Scenario 1: Free Plan (No Stripe)
✅ User clicks "Current Plan" or "Downgrade to Free"
✅ Works immediately, no Stripe needed
✅ User stays on Free plan

### Scenario 2: Paid Plan (Stripe Configured)
✅ Admin sets Stripe price IDs in Admin > Plans
✅ User clicks "Get Pro"
✅ Redirects to Stripe Checkout
✅ User completes payment
✅ Subscription created in database
✅ User has access to Pro features

### Scenario 3: Paid Plan (Stripe NOT Configured)
✅ Admin hasn't set Stripe price IDs
✅ User clicks "Get Pro"
✅ Shows error: "The Pro plan is not yet available for purchase..."
✅ Admin sees detailed message with setup instructions

### Scenario 4: Existing Features Still Work
✅ File upload (respects plan storage limit)
✅ Batch conversion (respects plan batch size)
✅ Daily conversion limit enforcement
✅ Queue job processing
✅ File download
✅ Preview generation

## Admin Panel Integration

### Admin Warnings
When viewing `/pricing` as admin, you'll see:

**Red Warning** - if STRIPE_KEY or STRIPE_SECRET not set:
```
⚠️ Admin Alert: Stripe credentials are not configured. 
Set STRIPE_KEY and STRIPE_SECRET in your .env file.
```

**Yellow Warning** - if plan Stripe price IDs are missing:
```
⚠️ Admin Alert: The following plans are missing Stripe price IDs: Pro, Business. 
Configure Plans in Admin > Plans.
```

### Admin Plan Editor
Admin > Plans > Edit [Plan]

New fields for each plan:
- Stripe Monthly Price ID (text field)
- Stripe Yearly Price ID (text field)

Just paste the price IDs from Stripe Dashboard.

## Error Handling

### User Errors
- **"The [Plan] is not yet available for purchase"** - Admin hasn't configured Stripe price IDs
- **"Stripe is not properly configured"** - STRIPE_KEY/SECRET missing from .env
- **"Failed to change your plan"** - Stripe API error (admin sees details)
- **"Failed to start checkout"** - Stripe API error (admin sees details)

### Admin-Only Debug Info
When admin encounters an error, they see:
- Exact error messages from Stripe API
- Which price IDs are missing
- Current .env configuration status

## Moving to Production

1. **Get Live Keys:**
   - Go to https://dashboard.stripe.com/apikeys
   - Switch from Test to Live mode (top right)
   - Copy Live keys (`pk_live_`, `sk_live_`)

2. **Update .env:**
   ```env
   STRIPE_KEY=pk_live_your_live_key
   STRIPE_SECRET=sk_live_your_live_secret
   STRIPE_WEBHOOK_SECRET=whsec_live_xxxxxxxxxxxx
   ```

3. **Create Live Products:**
   - In Stripe Dashboard, ensure you're in Live mode
   - Create the same products (Pro, Business)
   - Create the same prices (monthly, yearly)
   - Get the live price IDs

4. **Update Plans in Admin:**
   - Go to Admin > Plans
   - Update Pro and Business with Live price IDs

5. **Configure Webhook:**
   - Go to https://dashboard.stripe.com/webhooks
   - Add new endpoint
   - URL: `https://yourdomain.com/stripe/webhook`
   - Select events: 
     - `customer.subscription.updated`
     - `customer.subscription.deleted`
     - `invoice.payment_succeeded`
   - Copy webhook secret to .env

6. **Test:**
   - Use real credit card (charges $1 then refunds)
   - Or use Stripe test card with Live keys (some test cards work in Live mode)

## File Locations Modified

- `.env` - Add STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET
- `app/Http/Controllers/PlanController.php` - Enhanced checkout with error handling
- `app/Models/User.php` - Fixed activePlan() fallback
- `database/seeders/PlansSeeder.php` - Added nullable stripe price ID fields
- `resources/views/plans/index.blade.php` - Added admin warnings

## Support

### Common Issues

**Q: "This plan is not yet available" even though Stripe is configured**
A: Check Admin > Plans. Verify Stripe price IDs are entered correctly for monthly AND yearly.

**Q: Webhook not working in production**
A: Ensure HTTPS is enabled and accessible from internet. Check webhook secret in .env matches Stripe Dashboard.

**Q: Test card declined**
A: Use `4242 4242 4242 4242` with any future expiry and 3-digit CVC.

**Q: User can see "Stripe is not properly configured" message**
A: They shouldn't. This only shows to admins. If they see it, check STRIPE_KEY/SECRET in .env.

## Technical Details

### Cashier Integration
- Uses Laravel Cashier v15
- Stores subscription data in `subscriptions` table
- Webhook handler automatically processes Stripe events
- Price IDs link plans to Stripe products

### Database Fields
```
plans.stripe_monthly_price_id (nullable string)
plans.stripe_yearly_price_id (nullable string)
subscriptions.stripe_id (unique, links to Stripe subscription)
subscriptions.stripe_price (the price ID for the subscription)
```

### Plan Lookup
When user has active subscription:
1. Get stripe_price from subscriptions table
2. Find plan where stripe_monthly_price_id OR stripe_yearly_price_id matches
3. Return that plan (or fallback to Free if not found)

## Next Steps

1. Set STRIPE_KEY and STRIPE_SECRET in .env
2. Create products & prices in Stripe Dashboard
3. Enter price IDs in Admin > Plans
4. Test at /pricing
5. Deploy to production
