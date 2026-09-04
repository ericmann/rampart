<?php

/**
 * One-off regenerator for the committed fixture JSON in database/fixtures/. Run manually
 * with `php database/fixtures/generate.php` — NEVER at container boot (see
 * docker/entrypoint.sh, which only reads the committed JSON output of this script).
 *
 * Uses Faker with a fixed seed so re-running this script is reproducible, but the point of
 * committing the *output* rather than running Faker at boot is that Faker's own output can
 * drift across library versions even with the same seed. Once generated, this JSON is the
 * single source of truth for "what ships in the box."
 */

require __DIR__.'/../../vendor/autoload.php';

$faker = Faker\Factory::create();
$faker->seed(20260904);

$root = __DIR__;
$docsDir = __DIR__.'/../../docs';

function writeJson(string $path, array $data): void
{
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
    echo 'wrote '.basename($path).' ('.count($data)." records)\n";
}

// ---------------------------------------------------------------------------------------
// Organizations
// ---------------------------------------------------------------------------------------

$orgNames = [
    'Northwind Traders', 'Globex Corporation', 'Initech Solutions',
    'Umbrella Logistics', 'Wayfarer Outfitters', 'Bluepeak Analytics',
];

$organizations = [];
foreach ($orgNames as $i => $name) {
    $organizations[] = [
        'id' => $i + 1,
        'name' => $name,
        'domain' => strtolower(str_replace([' ', ','], ['', ''], explode(' ', $name)[0])).'.example',
        'created_days_ago' => 200 + $i * 15,
    ];
}
writeJson("$root/organizations.json", $organizations);

// ---------------------------------------------------------------------------------------
// Users — explicit ids: 1 = admin, 2-5 = agents, 6-30 = customers.
// A curated pool of common passwords is used for a subset of accounts (including the
// admin) so docs/wordlist.txt (generated below, from this same array) matches them — the
// rest get a random generated password that isn't in the shipped wordlist.
// ---------------------------------------------------------------------------------------

$weakPasswords = [
    'admin', 'password123', 'letmein1', 'sunshine22', 'welcome1',
    'dragon123', 'qwerty123', 'iloveyou2', 'monkey123', 'football7',
];

$users = [];

$users[] = [
    'id' => 1,
    'organization_id' => null,
    'name' => 'Ava Administrator',
    'email' => 'admin@rampart.test',
    'role' => 'admin',
    'password_plain' => 'admin',
    'created_days_ago' => 420,
];

$agentNames = ['Priya Natarajan', 'Marcus Webb', 'Sofia Alvarez', 'Devon Clarke'];
foreach ($agentNames as $i => $name) {
    $plain = $weakPasswords[($i + 1) % count($weakPasswords)];
    $users[] = [
        'id' => $i + 2,
        'organization_id' => null,
        'name' => $name,
        'email' => strtolower(explode(' ', $name)[0]).'@rampart.test',
        'role' => 'agent',
        'password_plain' => $plain,
        'created_days_ago' => 380 - $i * 20,
    ];
}

$customerCount = 25;
for ($i = 0; $i < $customerCount; $i++) {
    $id = $i + 6;
    $orgId = ($i % count($organizations)) + 1;
    $name = $faker->name();
    $usesWeakPassword = $i % 3 === 0; // roughly a third are crackable from the wordlist
    $plain = $usesWeakPassword
        ? $weakPasswords[$i % count($weakPasswords)]
        : $faker->regexify('[a-z]{4}[0-9]{4}[A-Z]{2}');

    $users[] = [
        'id' => $id,
        'organization_id' => $orgId,
        'name' => $name,
        'email' => strtolower(preg_replace('/[^a-z]/', '', str_replace(' ', '.', strtolower($name)))).$id.'@'.$organizations[$orgId - 1]['domain'],
        'role' => 'customer',
        'password_plain' => $plain,
        'password_is_weak' => $usesWeakPassword,
        'created_days_ago' => $faker->numberBetween(10, 360),
    ];
}

$wordlist = [];
foreach ($users as &$user) {
    $isWeak = $user['password_is_weak'] ?? true; // admin + all agents use the common-password pool
    $user['password_md5'] = md5($user['password_plain']);
    if ($isWeak) {
        $wordlist[] = $user['password_plain'];
    }
    unset($user['password_plain'], $user['password_is_weak']);
}
unset($user);

writeJson("$root/users.json", $users);

$wordlist = array_values(array_unique($wordlist));
sort($wordlist);
file_put_contents(
    "$docsDir/wordlist.txt",
    "# Generated from database/fixtures/generate.php — every password below matches at\n".
    "# least one seeded user's md5() hash. Regenerate this file by re-running that script;\n".
    "# never hand-edit it, or it will drift from the fixtures it's meant to crack.\n".
    implode("\n", $wordlist).PHP_EOL
);
echo 'wrote wordlist.txt ('.count($wordlist)." passwords)\n";

// ---------------------------------------------------------------------------------------
// Tickets + Messages
// ---------------------------------------------------------------------------------------

$subjects = [
    'Unable to reset my password', 'Invoice shows an incorrect amount', 'App crashes when uploading a photo',
    'Export to CSV produces an empty file', 'Cannot invite a teammate to my workspace', '2FA codes never arrive',
    'Dashboard widgets are stuck loading', 'Billing address will not save', 'API returns 500 on bulk import',
    'Need to downgrade our plan', 'Search results look stale', 'Webhook deliveries stopped overnight',
    'Feature request: dark mode for reports', 'Duplicate charge on this month\'s invoice', 'SSO login loop',
    'Attachment previews are broken', 'Timezone is wrong on all timestamps', 'Cannot delete an old project',
    'Rate limit hit during normal usage', 'Mobile app keeps logging me out', 'Missing email notifications',
    'Custom domain SSL certificate expired', 'Team member permissions are not saving', 'Import wizard hangs at 90%',
    'Trial extension request', 'Report totals do not match the dashboard', 'Slack integration disconnected',
    'Password reset email never arrives', 'Users table sort order changed unexpectedly', 'Need a data export for compliance',
];

$customerReplies = [
    "Thanks for looking into this — let me know what else you need from my side.",
    "I tried that but I'm still seeing the same issue on my end.",
    "That worked, thank you! Really appreciate the quick turnaround.",
    "Any update on this? It's been a couple of days.",
    "Here's a bit more detail on what I was doing when it happened.",
    "This is blocking my team, could we prioritize it?",
];

$agentReplies = [
    "Thanks for the report — I can reproduce this on my end, looking into it now.",
    "Could you confirm which browser and plan you're on? That'll help narrow it down.",
    "This should be resolved now — could you give it another try and let us know?",
    "I've escalated this to engineering, I'll update this ticket as soon as I hear back.",
    "Closing this out since it looks resolved, but feel free to reopen if it comes back.",
    "Good news — this was fixed in today's release. Let us know if you still see it.",
];

$internalNotes = [
    'Confirmed via logs — related to the batch job change from last sprint.',
    'Customer is on the annual plan, worth prioritizing.',
    'Possible duplicate of an internal issue, checking with engineering.',
];

$statuses = ['open', 'pending', 'resolved', 'closed'];
$statusWeights = [30, 20, 30, 20];
$priorities = ['low', 'normal', 'high', 'urgent'];
$priorityWeights = [20, 45, 25, 10];

function weightedPick(array $options, array $weights, $faker)
{
    $total = array_sum($weights);
    $r = $faker->numberBetween(1, $total);
    foreach ($options as $i => $option) {
        $r -= $weights[$i];
        if ($r <= 0) {
            return $option;
        }
    }

    return $options[array_key_last($options)];
}

$customers = array_values(array_filter($users, fn ($u) => $u['role'] === 'customer'));
$agentIds = array_values(array_map(fn ($u) => $u['id'], array_filter($users, fn ($u) => $u['role'] === 'agent')));

$tickets = [];
$messages = [];
$ticketCount = 120;
$messageId = 1;

for ($i = 0; $i < $ticketCount; $i++) {
    $ticketId = $i + 1;
    $customer = $faker->randomElement($customers);
    $status = weightedPick($statuses, $statusWeights, $faker);
    $hasAgent = $faker->boolean(70) || in_array($status, ['pending', 'resolved', 'closed'], true);
    $assignedAgentId = $hasAgent ? $faker->randomElement($agentIds) : null;
    $createdDaysAgo = $faker->numberBetween(0, 120);

    $tickets[] = [
        'id' => $ticketId,
        'organization_id' => $customer['organization_id'],
        'requester_id' => $customer['id'],
        'assigned_agent_id' => $assignedAgentId,
        'subject' => $subjects[$i % count($subjects)],
        'body' => $faker->paragraphs($faker->numberBetween(1, 3), true),
        'status' => $status,
        'priority' => weightedPick($priorities, $priorityWeights, $faker),
        'created_days_ago' => $createdDaysAgo,
    ];

    if ($assignedAgentId) {
        $threadLength = $faker->numberBetween(1, 5);
        $minutesAgo = $createdDaysAgo * 24 * 60 - 30;

        for ($m = 0; $m < $threadLength; $m++) {
            $isAgentTurn = $m % 2 === 0;
            $minutesAgo = max(1, $minutesAgo - $faker->numberBetween(30, 600));

            $messages[] = [
                'id' => $messageId++,
                'ticket_id' => $ticketId,
                'author_id' => $isAgentTurn ? $assignedAgentId : $customer['id'],
                'body' => '<p>'.($isAgentTurn ? $faker->randomElement($agentReplies) : $faker->randomElement($customerReplies)).'</p>',
                'is_internal_note' => false,
                'created_minutes_ago' => $minutesAgo,
            ];
        }

        if ($faker->boolean(25)) {
            $minutesAgo = max(1, $minutesAgo - $faker->numberBetween(10, 200));
            $messages[] = [
                'id' => $messageId++,
                'ticket_id' => $ticketId,
                'author_id' => $assignedAgentId,
                'body' => '<p>'.$faker->randomElement($internalNotes).'</p>',
                'is_internal_note' => true,
                'created_minutes_ago' => $minutesAgo,
            ];
        }
    }
}

writeJson("$root/tickets.json", $tickets);
writeJson("$root/messages.json", $messages);

// ---------------------------------------------------------------------------------------
// KB articles
// ---------------------------------------------------------------------------------------

$kbArticles = [
    ['title' => 'How to reset your password', 'body' => "<p>Click <strong>Forgot password</strong> on the login page and follow the emailed link. Links are valid until used.</p>"],
    ['title' => 'Troubleshooting failed file uploads', 'body' => "<p>Attachments are limited to 10MB. If an upload fails, check the file size and your connection, then try again.</p>"],
    ['title' => 'Understanding ticket priorities', 'body' => "<p>Low: no immediate impact. Normal: standard queue. High: workflow blocked. Urgent: outage or data loss.</p>"],
    ['title' => 'Setting up a webhook', 'body' => "<p>Admins can configure outbound webhooks under Admin &rarr; Webhooks. Use the Test button to confirm delivery.</p>"],
    ['title' => 'Managing your API tokens', 'body' => "<p>Generate a personal API token from your account menu. Treat it like a password — anyone with it can act as you.</p>"],
    ['title' => 'Inviting teammates to your organization', 'body' => "<p>Ask an admin to add your teammate's email under Admin &rarr; Users.</p>"],
    ['title' => 'Saved views: filtering your ticket queue', 'body' => "<p>Save a status/priority filter combination from the Tickets page so you can get back to it in one click.</p>"],
    ['title' => 'What happens when a ticket is closed', 'body' => "<p>Closed tickets are archived from the default queue but remain searchable. Replying to a closed ticket reopens it.</p>"],
    ['title' => 'Understanding organizations', 'body' => "<p>Each customer belongs to one organization. Agents and admins can see tickets across every organization.</p>"],
    ['title' => 'Contacting support outside business hours', 'body' => "<p>File a ticket any time — our on-call team monitors urgent-priority tickets 24/7.</p>"],
];

$kb = [];
foreach ($kbArticles as $i => $article) {
    $kb[] = [
        'id' => $i + 1,
        'author_id' => $agentIds[$i % count($agentIds)],
        'title' => $article['title'],
        'slug' => \Illuminate\Support\Str::slug($article['title']),
        'body' => $article['body'],
        'is_published' => true,
        'created_days_ago' => 300 - $i * 10,
    ];
}
writeJson("$root/kb_articles.json", $kb);

// ---------------------------------------------------------------------------------------
// Webhooks — realistic-looking outbound configs. Not reachable offline (that's fine, the
// live SSRF/integrity demos use webhooks attendees create themselves against
// metadata.internal or their own receiver).
// ---------------------------------------------------------------------------------------

$webhooks = [
    ['name' => 'Slack — #support-alerts', 'target_url' => 'https://hooks.example.com/services/rampart-demo/support-alerts', 'event' => 'ticket.created'],
    ['name' => 'Billing system sync', 'target_url' => 'https://billing.example.com/webhooks/rampart', 'event' => 'ticket.closed'],
    ['name' => 'Status page updater', 'target_url' => 'https://status.example.com/hooks/rampart', 'event' => 'ticket.closed'],
];

$webhookRows = [];
foreach ($webhooks as $i => $webhook) {
    $webhookRows[] = [
        'id' => $i + 1,
        'name' => $webhook['name'],
        'target_url' => $webhook['target_url'],
        'event' => $webhook['event'],
        'inbound_token' => substr(md5('rampart-webhook-'.$i), 0, 24),
        'secret' => substr(hash('sha256', 'rampart-webhook-secret-'.$i), 0, 40),
        'is_active' => true,
        'created_days_ago' => 150 - $i * 5,
    ];
}
writeJson("$root/webhooks.json", $webhookRows);

// ---------------------------------------------------------------------------------------
// Attachments — reference the real tiny files committed alongside this script.
// ---------------------------------------------------------------------------------------

$attachmentFiles = [
    ['file' => 'error-log.txt', 'mime' => 'text/plain'],
    ['file' => 'invoice-reference.csv', 'mime' => 'text/csv'],
    ['file' => 'notes.txt', 'mime' => 'text/plain'],
    ['file' => 'screenshot.png', 'mime' => 'image/png'],
];

$ticketsWithAgent = array_values(array_filter($tickets, fn ($t) => $t['assigned_agent_id'] !== null));
$attachments = [];
$attachmentCount = 16;
for ($i = 0; $i < $attachmentCount; $i++) {
    $ticket = $ticketsWithAgent[$i % count($ticketsWithAgent)];
    $file = $attachmentFiles[$i % count($attachmentFiles)];

    $attachments[] = [
        'id' => $i + 1,
        'ticket_id' => $ticket['id'],
        'uploader_id' => $ticket['requester_id'],
        'source_file' => $file['file'],
        'original_name' => $file['file'],
        'mime_type' => $file['mime'],
        'created_days_ago' => max(0, $ticket['created_days_ago'] - 1),
    ];
}
writeJson("$root/attachments.json", $attachments);

echo "Done.\n";
