<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Ticket #{{ $ticket->id }} — Status Updated</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background-color: #f4f4f5; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        .wrapper { max-width: 620px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); padding: 32px 40px; position: relative; }
        .header-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; }
        .header-accent.resolved   { background: linear-gradient(90deg, #16a34a, #4ade80); }
        .header-accent.closed     { background: linear-gradient(90deg, #52525b, #a1a1aa); }
        .header-accent.in_progress{ background: linear-gradient(90deg, #7c3aed, #a78bfa); }
        .header-accent.waiting    { background: linear-gradient(90deg, #d97706, #fbbf24); }
        .header-accent.default    { background: linear-gradient(90deg, #f37021, #ff913b); }
        .header-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
        .header-logo-icon { width: 36px; height: 36px; background: #f37021; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .header-logo-text { color: #ffffff; font-size: 16px; font-weight: 700; letter-spacing: -0.3px; }
        .header-title { color: #ffffff; font-size: 22px; font-weight: 700; line-height: 1.3; letter-spacing: -0.4px; }
        .header-subtitle { color: #a1a1aa; font-size: 14px; margin-top: 6px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 16px; color: #18181b; font-weight: 600; margin-bottom: 12px; }
        .intro { font-size: 14px; color: #52525b; line-height: 1.7; margin-bottom: 28px; }
        .status-flow { display: flex; align-items: center; justify-content: center; gap: 0; margin-bottom: 32px; }
        .status-pill { padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; text-align: center; min-width: 130px; }
        .status-pill.old { background: #f4f4f5; color: #71717a; border: 1px solid #e4e4e7; text-decoration: line-through; opacity: 0.7; }
        .status-pill.new-resolved   { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .status-pill.new-closed     { background: #f4f4f5; color: #52525b; border: 1px solid #d4d4d8; }
        .status-pill.new-in_progress{ background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; }
        .status-pill.new-waiting    { background: #fef9c3; color: #a16207; border: 1px solid #fef08a; }
        .status-pill.new-new        { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .status-pill.new-suspended  { background: #f4f4f5; color: #52525b; border: 1px solid #d4d4d8; }
        .status-arrow { font-size: 20px; color: #d4d4d8; margin: 0 12px; font-weight: 300; }
        .ticket-meta { background: #fafafa; border: 1px solid #e4e4e7; border-radius: 12px; padding: 20px; margin-bottom: 28px; }
        .ticket-meta-row { display: flex; justify-content: space-between; font-size: 13px; }
        .ticket-meta-label { color: #71717a; font-weight: 500; }
        .ticket-meta-value { color: #18181b; font-weight: 600; }
        .resolved-callout { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px 24px; margin-bottom: 28px; }
        .resolved-callout-title { font-size: 15px; font-weight: 700; color: #15803d; margin-bottom: 6px; }
        .resolved-callout-text { font-size: 13px; color: #166534; line-height: 1.7; }
        .cta-wrapper { text-align: center; margin-bottom: 28px; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #f37021 0%, #e25813 100%); color: #ffffff !important; text-decoration: none; font-size: 15px; font-weight: 600; padding: 14px 36px; border-radius: 10px; letter-spacing: -0.2px; box-shadow: 0 4px 12px rgba(243,112,33,0.35); }
        .cta-button.green { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); box-shadow: 0 4px 12px rgba(22,163,74,0.30); }
        .divider { border: none; border-top: 1px solid #e4e4e7; margin: 28px 0; }
        .footer { padding: 0 40px 32px; }
        .footer-text { font-size: 12px; color: #a1a1aa; line-height: 1.7; text-align: center; }
        .footer-link { color: #f37021; text-decoration: none; }
        .footer-brand { display: flex; align-items: center; justify-content: center; gap: 6px; margin-bottom: 12px; }
        .footer-brand-dot { width: 6px; height: 6px; background: #f37021; border-radius: 50%; }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <div class="header-accent {{ $accentClass }}"></div>
            <div class="header-logo">
                <div class="header-logo-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                </div>
                <span class="header-logo-text">REGANTA Helpdesk</span>
            </div>
            <div class="header-title">Ticket Status Updated</div>
            <div class="header-subtitle">Your support request has moved to a new stage.</div>
        </div>

        <!-- Body -->
        <div class="body">
            <div class="greeting">Hello, {{ $notifiable->name }}!</div>
            <p class="intro">
                The status of your IT support ticket <strong>#{{ $ticket->id }}</strong> has been updated by our team.
            </p>

            <!-- Status Flow Visualiser -->
            <div class="status-flow">
                <div class="status-pill old">{{ $oldStatus->getLabel() }}</div>
                <div class="status-arrow">&#8594;</div>
                <div class="status-pill {{ $newStatusClass }}">{{ $newStatus->getLabel() }}</div>
            </div>

            <!-- Ticket Meta -->
            <div class="ticket-meta">
                <div class="ticket-meta-row" style="margin-bottom: 10px;">
                    <span class="ticket-meta-label">Ticket ID</span>
                    <span class="ticket-meta-value" style="font-family: monospace;">#{{ $ticket->id }}</span>
                </div>
                <div class="ticket-meta-row" style="margin-bottom: 10px;">
                    <span class="ticket-meta-label">Category</span>
                    <span class="ticket-meta-value">{{ $ticket->category->getLabel() }}</span>
                </div>
                <div class="ticket-meta-row">
                    <span class="ticket-meta-label">New Status</span>
                    <span class="ticket-meta-value">{{ $newStatus->getLabel() }}</span>
                </div>
            </div>

            <!-- Special callout if resolved -->
            @if ($isResolved)
                <div class="resolved-callout">
                    <div class="resolved-callout-title">&#10003; &nbsp;Your issue has been resolved!</div>
                    <p class="resolved-callout-text">
                        Our team has marked this ticket as resolved. Please click the button below to verify the
                        solution and rate your experience. Your feedback helps us improve.
                    </p>
                </div>
            @endif

            <!-- CTA -->
            <div class="cta-wrapper">
                @if ($isResolved)
                    <a href="{{ $ticketUrl }}" class="cta-button green">Rate &amp; Close Ticket &rarr;</a>
                @else
                    <a href="{{ $ticketUrl }}" class="cta-button">View Ticket #{{ $ticket->id }} &rarr;</a>
                @endif
            </div>

            <hr class="divider" />

            <p style="font-size: 13px; color: #71717a; line-height: 1.7; text-align: center;">
                If you did not expect this email, you can safely ignore it.<br />
                This notification was sent to <strong>{{ $notifiable->email }}</strong>.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-brand">
                <div class="footer-brand-dot"></div>
                <span style="font-size: 12px; color: #a1a1aa; font-weight: 600;">REGANTA IT Helpdesk</span>
                <div class="footer-brand-dot"></div>
            </div>
            <p class="footer-text">
                &copy; {{ date('Y') }} REGANTA. All rights reserved.<br />
                <a href="{{ url('/dashboard') }}" class="footer-link">Go to Dashboard</a>
            </p>
        </div>
    </div>
</body>
</html>
