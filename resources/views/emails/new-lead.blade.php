<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Lead Received</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h1 style="color: #1a202c;">New Lead Received</h1>
    <p>A new quote inquiry has been submitted through the DigitalBuilders website.</p>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0; font-weight: bold; background: #f7fafc;">Lead ID</td>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0;">{{ $leadId ?? 'Pending' }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0; font-weight: bold; background: #f7fafc;">Source</td>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0;">{{ $source }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0; font-weight: bold; background: #f7fafc;">Name</td>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0;">{{ $leadName }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0; font-weight: bold; background: #f7fafc;">Email</td>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0;">{{ $leadEmail }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0; font-weight: bold; background: #f7fafc;">Phone</td>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0;">{{ $leadPhone }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0; font-weight: bold; background: #f7fafc;">Project Type</td>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0;">{{ $projectType }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0; font-weight: bold; background: #f7fafc;">Status</td>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0; text-transform: capitalize;">{{ $leadStatus }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0; font-weight: bold; background: #f7fafc;">Submitted</td>
            <td style="padding: 8px 12px; border: 1px solid #e2e8f0;">{{ $submittedAt }}</td>
        </tr>
    </table>

    @if($description)
        <h3>Project Description</h3>
        <p>{{ $description }}</p>
    @endif

    <p style="margin-top: 24px;">
        <a href="mailto:{{ $leadEmail }}" style="background: #2563eb; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 6px;">
            Reply to {{ $leadName }}
        </a>
    </p>

    <hr style="margin-top: 32px; border-color: #e2e8f0;">
    <p style="font-size: 12px; color: #a0aec0;">DigitalBuilders System</p>
</body>
</html>
