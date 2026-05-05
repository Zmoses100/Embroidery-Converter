# PayPal Payment Integration Setup

This guide explains how to set up PayPal payment support for the Embroidery Converter application. PayPal is available as an alternative payment method alongside Stripe for Pro and Business plans.

## Overview

- **Payment Method**: PayPal REST API (subscription-based)
- **Credentials**: Client ID and Client Secret (no password storage)
- **Modes**: Sandbox (development) and Live (production)
- **Plans Supported**: Pro and Business (Free plan does not use PayPal)
- **Billing Intervals**: Monthly and Yearly

## Prerequisites

1. PayPal Business account
2. Developer console access at https://developer.paypal.com/dashboard/
3. Laravel 11 with the embroidery-converter app running

## Step 1: Get PayPal Credentials

### For Sandbox (Development/Testing)

1. Go to https://developer.paypal.com/dashboard/
2. Sign in with your PayPal Business account
3. In the top-right, select **Sandbox** environment
4. Navigate to **Apps & Credentials** tab
5. Under the "Rest API apps" section, you should see a "Default Application"
6. Click the "Default Application" to view credentials:
   - **Client ID** (starts with `AXxxxx...`)
   - **Secret** (click "Show" to reveal)

### For Production (Live)

1. In the Developer Dashboard, select **Live** environment
2. Navigate to **Apps & Credentials** tab
3. Under "Rest API apps", click your app to view:
   - **Client ID** (production)
   - **Secret** (production)

## Step 2: Configure Environment Variables

1. Open your `.env` file (create from `.env.example` if needed)
2. Add the following PayPal configuration:

```env
# For local development, use sandbox mode
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=your-sandbox-client-id
PAYPAL_CLIENT_SECRET=your-sandbox-secret
PAYPAL_CURRENCY=USD

# For production, change to:
# PAYPAL_MODE=live
# PAYPAL_CLIENT_ID=your-live-client-id
# PAYPAL_CLIENT_SECRET=your-live-secret
```

3. Save the file

## Step 3: Run Database Migrations

The PayPal integration requires new database tables:

```bash
php artisan migrate
```

This will create:
- `paypal_product_id` field in `plans` table
- `paypal_plan_id_monthly` and `paypal_plan_id_yearly` fields in `plans` table
- `paypal_transactions` table to track PayPal payments/subscriptions

## Step 4: Set Up PayPal Products and Plans

PayPal Subscription Plans are created automatically when users first try to checkout with a specific billing interval. However, you can manually create them through the dashboard:

### Automatic Setup (Recommended)

1. Log in to the app as an admin
2. Go to **Pricing** page
3. As a logged-in user, click "Get [Plan Name] (PayPal)" button
4. You'll be redirected to PayPal approval
5. The integration automatically creates the product and plan in PayPal

### Manual Setup (Optional)

If you prefer to manually create PayPal products and plans:

1. Go to PayPal Developer Dashboard
2. Navigate to **Products** > **Catalog**
3. Create a product for each subscription plan:
   - Name: "Pro Plan", "Business Plan", etc.
   - Type: Service
4. Create billing plans:
   - Link to the product
   - Set billing frequency (monthly/yearly)
   - Set price matching your plan
5. In your admin panel, paste the Product IDs and Plan IDs into the plan settings

## Step 5: Enable PayPal in Admin Plans

1. Log in as admin
2. Go to **Admin** > **Plans**
3. For each paid plan (Pro, Business):
   - Enter the **PayPal Product ID** (if using manual setup)
   - Enter **PayPal Plan ID (Monthly)** (if using manual setup)
   - Enter **PayPal Plan ID (Yearly)** (if using manual setup)
4. Save the plan

**Note**: If you don't have Product/Plan IDs, leave them blank. They will be created automatically on first checkout.

## Step 6: Test PayPal Checkout

### Create a PayPal Sandbox Buyer Account

1. Go to PayPal Developer Dashboard
2. In **Sandbox** environment, go to **Accounts**
3. Create a "Personal" account (if not already created):
   - This is your test buyer account
   - Note the email and password (generated)

### Test Checkout Flow

1. In your local app, go to **Pricing** page
2. Click "Get [Plan] (PayPal)" for a paid plan
3. You'll be redirected to PayPal sandbox
4. Log in with the **sandbox buyer account** credentials
5. Approve the subscription
6. You'll be redirected back to your app with success message
7. Your account should now be on the paid plan

## Step 7: Monitor PayPal Transactions

### In the App

1. PayPal transactions are logged to `storage/logs/laravel.log`
2. Transaction details are stored in the `paypal_transactions` table:
   - User ID and Plan ID
   - PayPal Subscription ID
   - Status (pending, active, cancelled, failed)
   - Amount and currency
   - Activation and cancellation dates

### In PayPal Dashboard

1. Go to PayPal Developer Dashboard
2. **Sandbox** environment > **Transactions**
3. View all subscription transactions
4. Check payment history and status

## Troubleshooting

### "PayPal payment is not available at this time"

- **Cause**: PayPal credentials are not configured
- **Fix**: Verify `PAYPAL_CLIENT_ID` and `PAYPAL_CLIENT_SECRET` are set in `.env`

### "Failed to start PayPal checkout"

- **Cause**: PayPal API request failed
- **Action**: 
  1. Check `storage/logs/laravel.log` for specific error
  2. Verify credentials are correct
  3. Verify `PAYPAL_MODE` matches your credentials (sandbox vs live)
  4. Check PayPal API status: https://status.paypal.com/

### "Subscription is not active"

- **Cause**: User didn't complete PayPal approval or approval failed
- **Action**: 
  1. Ask user to try again
  2. Check PayPal transaction status in sandbox dashboard
  3. Review logs for specific PayPal response

### "PayPal credentials not configured" in logs

- **Cause**: `.env` file is missing PayPal configuration
- **Fix**: Add all `PAYPAL_*` variables to `.env`

### User sees no PayPal button on pricing page

- **Cause**: `PAYPAL_CLIENT_ID` is not configured
- **Fix**: Set `PAYPAL_CLIENT_ID` in `.env` (even if empty, button won't show)

## Switching from Sandbox to Production

1. Create production credentials in PayPal Business Dashboard
2. Update `.env`:
   ```env
   PAYPAL_MODE=live
   PAYPAL_CLIENT_ID=your-production-client-id
   PAYPAL_CLIENT_SECRET=your-production-secret
   ```
3. Restart the application
4. Test with a small trial purchase first
5. Monitor `storage/logs/laravel.log` for any production issues

## Plan Limits After PayPal Payment

Once a user subscribes via PayPal, their plan limits are enforced automatically:

- **Free**: 5 conversions/day, 100 MB storage, batch size 1
- **Pro**: 100 conversions/day, 1 GB storage, batch size 10
- **Business**: Unlimited conversions, 10 GB storage, batch size 50

These limits are checked during file upload and conversion operations.

## Admin Features

### Viewing PayPal Transactions

Admin users can view PayPal transactions by:
1. Checking `paypal_transactions` table in database
2. Reviewing logs in `storage/logs/laravel.log`
3. Checking subscription status in PayPal Dashboard

### Handling Failed Payments

If a PayPal subscription payment fails:
1. User will see a warning in their dashboard (implementation pending)
2. Admin can see the failed transaction in logs
3. PayPal will retry the payment automatically

### Cancelling a PayPal Subscription

Currently, users can downgrade to Free plan to cancel PayPal subscriptions. Future versions may include ability to pause/resume subscriptions.

## Security Notes

- ✅ Client Secret is never exposed to the browser
- ✅ No PayPal password is stored
- ✅ All API calls are server-side only
- ✅ Transaction IDs are validated against logged-in user ID
- ✅ Webhook requests should be verified (TODO: implement signature verification)

## Environment Variables Reference

| Variable | Example | Description |
|----------|---------|-------------|
| `PAYPAL_MODE` | `sandbox` or `live` | PayPal API environment |
| `PAYPAL_CLIENT_ID` | `AXjxxxx...` | OAuth Client ID from PayPal Dashboard |
| `PAYPAL_CLIENT_SECRET` | `ECjxxxx...` | OAuth Client Secret (keep secret!) |
| `PAYPAL_CURRENCY` | `USD` | Currency code for transactions |
| `PAYPAL_WEBHOOK_SECRET` | `xxxx...` | (Optional) For webhook verification |

## File Changes

The following files were added/modified:

### New Files
- `app/Services/PayPalService.php` - PayPal API wrapper
- `app/Http/Controllers/PayPalController.php` - Checkout/callback handlers
- `app/Models/PaypalTransaction.php` - Transaction record model
- `config/services.php` - PayPal configuration
- `database/migrations/2026_05_04_000000_add_paypal_to_plans_table.php`
- `database/migrations/2026_05_04_000001_create_paypal_transactions_table.php`
- `PAYPAL_SETUP.md` - This file

### Modified Files
- `app/Models/Plan.php` - Added PayPal fields and relationships
- `app/Models/User.php` - Added paypal_transactions relationship
- `resources/views/plans/index.blade.php` - Added PayPal payment button
- `routes/web.php` - Added PayPal checkout/webhook routes
- `.env.example` - Added PayPal configuration variables

## Support

For issues or questions:
1. Check the Troubleshooting section above
2. Review logs: `storage/logs/laravel.log`
3. Check PayPal API status: https://status.paypal.com/
4. Review PayPal Developer Docs: https://developer.paypal.com/docs/api/

## Next Steps

Consider implementing:
- [ ] Webhook signature verification
- [ ] Subscription pause/resume functionality
- [ ] Admin interface to manage PayPal transactions
- [ ] Failed payment retry dashboard notifications
- [ ] PayPal/Stripe subscription migration
- [ ] Invoice generation for PayPal subscriptions
