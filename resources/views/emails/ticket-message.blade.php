<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>New Reply — Ticket #{{ $ticket->id }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background-color: #f4f4f5; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        .wrapper { max-width: 620px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); padding: 32px 40px; position: relative; }
        .header-accent { position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #f37021, #ff913b); }
        .header-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
        .header-logo-icon { width: 36px; height: 36px; background: #f37021; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .header-logo-text { color: #ffffff; font-size: 16px; font-weight: 700; letter-spacing: -0.3px; }
        .header-title { color: #ffffff; font-size: 22px; font-weight: 700; line-height: 1.3; letter-spacing: -0.4px; }
        .header-subtitle { color: #a1a1aa; font-size: 14px; margin-top: 6px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 16px; color: #18181b; font-weight: 600; margin-bottom: 12px; }
        .intro { font-size: 14px; color: #52525b; line-height: 1.7; margin-bottom: 24px; }
        .ticket-card { background: #fafafa; border: 1px solid #e4e4e7; border-radius: 12px; overflow: hidden; margin-bottom: 28px; }
        .ticket-card-header { background: #f4f4f5; border-bottom: 1px solid #e4e4e7; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; }
        .ticket-id { font-size: 13px; font-weight: 700; color: #3f3f46; font-family: 'Courier New', monospace; }
        .ticket-badge { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; border-radius: 20px; font-size: 11px; font-weight: 600; padding: 3px 10px; letter-spacing: 0.5px; text-transform: uppercase; }
        .ticket-card-body { padding: 20px; }
        .author-row { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
        .author-avatar { width: 32px; height: 32px; background: linear-gradient(135deg, #f37021, #be4011); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; font-weight: 700; flex-shrink: 0; }
        .author-name { font-size: 14px; font-weight: 600; color: #18181b; }
        .author-role { font-size: 11px; color: #71717a; background: #dbeafe; color: #1e40af; border-radius: 20px; padding: 2px 8px; font-weight: 500; display: inline-block; margin-top: 2px; }
        .message-preview { background: #ffffff; border: 1px solid #e4e4e7; border-left: 3px solid #f37021; border-radius: 6px; padding: 14px 16px; font-size: 14px; color: #3f3f46; line-height: 1.7; font-style: italic; }
        .cta-wrapper { text-align: center; margin-bottom: 28px; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #f37021 0%, #e25813 100%); color: #ffffff !important; text-decoration: none; font-size: 15px; font-weight: 600; padding: 14px 36px; border-radius: 10px; letter-spacing: -0.2px; box-shadow: 0 4px 12px rgba(243,112,33,0.35); }
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
            <div class="header-accent"></div>
            <div class="header-logo">
                <div class="header-logo-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <span class="header-logo-text">REGANTA Helpdesk</span>
            </div>
            <div class="header-title">New Reply on Your Ticket</div>
            <div class="header-subtitle">An IT team member has responded to your request.</div>
        </div>

        <!-- Body -->
        <div class="body">
            <div class="greeting">Hello, {{ $notifiable->name }}!</div>
            <p class="intro">
                Your IT support ticket has received a new reply. Please log in to view the full message thread
                and respond if needed.
            </p>

            <!-- Ticket Preview Card -->
            <div class="ticket-card">
                <div class="ticket-card-header">
                    <span class="ticket-id">#{{ $ticket->id }} &mdash; {{ $ticket->category->getLabel() }}</span>
                    <span class="ticket-badge">IT Reply</span>
                </div>
                <div class="ticket-card-body">
                    <div class="author-row">
                        <div class="author-avatar">{{ strtoupper(substr($author->name, 0, 1)) }}</div>
                        <div>
                            <div class="author-name">{{ $author->name }}</div>
                            <span class="author-role">IT Staff</span>
                        </div>
                    </div>
                    <div class="message-preview">
                        &ldquo;{{ \Illuminate\Support\Str::limit($snippet, 280) }}&rdquo;
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="cta-wrapper">
                <a href="{{ $ticketUrl }}" class="cta-button">View Full Conversation &rarr;</a>
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
