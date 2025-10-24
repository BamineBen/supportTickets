<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nouveau message de contact</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }
        .container {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            background-color: #0d6efd;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px 5px 0 0;
            margin: -20px -20px 20px;
        }
        .field {
            margin-bottom: 15px;
        }
        .label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #0d6efd;
        }
        .content {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            white-space: pre-wrap;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.8em;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nouveau message de contact</h2>
        </div>
        
        <div class="field">
            <div class="label">Nom :</div>
            <div>{{ $data['name'] }}</div>
        </div>
        
        <div class="field">
            <div class="label">Email :</div>
            <div>{{ $data['email'] }}</div>
        </div>
        
        <div class="field">
            <div class="label">Sujet :</div>
            <div>{{ $data['subject'] }}</div>
        </div>
        
        <div class="field">
            <div class="label">Message :</div>
            <div class="content">{{ $data['message'] }}</div>
        </div>
        
        <div class="field">
            <div class="label">Date d'envoi :</div>
            <div>{{ now()->format('d/m/Y à H:i') }}</div>
        </div>
        
        <div class="footer">
            Ce message a été envoyé depuis le formulaire de contact de l'application Support Tickets.
        </div>
    </div>
</body>
</html>
