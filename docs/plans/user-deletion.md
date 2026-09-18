# User deletion — issues #48 and #50

## Agreed behavior

A User may permanently delete themselves even with active Matches, after the existing password confirmation. Delete their authentication/contact details, both Profiles, Books, Listings, Interests, Passes, every participating Match, and entire Conversations, including messages written by the other participant. Blocked history has no retention exception. The other participant's Profiles, Books, Listings, and unrelated Matches remain.

Removal appears on the other participant's next refresh/navigation. Subsequent requests to view, send to, or subscribe to a deleted Conversation must fail immediately. No real-time deletion notification is needed.

## Smallest implementation

1. Keep `DeleteUser`'s transaction and User lock. Remove Match ending/detachment and let existing foreign-key cascades delete the User's dependent records. Explicitly remove password-reset tokens by email and database sessions by User ID; retain logout and session invalidation, performing deletion successfully before logout.
2. Add a forward migration that deletes previously detached Matches and their Conversations, removes the retention fields, and restores required Match decision and message sender references with cascading deletion. Existing retained Conversations cannot be recovered after this migration.
3. Remove retained-Match branches from Match and Conversation controllers and authorization, along with their unused UI/types/translations. Keep normal missing-resource behavior for old links.
4. Update deletion confirmation and translations to explain permanence and loss of all Matches and Conversations for both participants. Keep Delete Book's active-Match restriction unchanged.
5. Replace retention expectations with focused regression checks for Author and Beta Reader deletion, including a User with both Profiles, active/ended Matches, messages from both participants, sessions/reset tokens, and preservation of another User's unrelated records. Check old Conversation view/send/subscription requests are denied, and run migration checks against previously detached records.

No stored uploads are currently implemented, and Block retention exists only in the glossary. Apply the same ownership rule if those features are introduced; no new cleanup service or event system is needed now.

## Issue interpretation

The loss described by #48 becomes intended behavior under this decision. #50 supplies the deletion contract; implementation should replace the retention workaround rather than add an active-Match guard.

See [the decision](../adr/0003-delete-user-conversations-without-retention.md) and [the glossary](../../CONTEXT.md).
