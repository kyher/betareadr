# Use Reverb for real-time conversation delivery

BetaReadr will use Laravel Reverb to deliver new Conversation messages in real time rather than relying on polling. Messages remain durable database records and broadcasts only notify connected clients, so Conversation history remains complete when Reverb is unavailable or a client reconnects; this accepts additional infrastructure and authorization complexity in exchange for responsive messaging and avoiding a later transport migration.
