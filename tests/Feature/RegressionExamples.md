# Adding regression tests as you fix bugs

The public suite (`tests/Unit` + `tests/Feature`, run with `composer test`) only checks that
Rampart *works*. It does not check that it's secure — that's what `tests/Exploits` is for,
and you won't be running that suite (see `docs/LAB-GUIDE.md`).

As you patch each vulnerability, add a regression test here in `tests/Feature` that would
**fail on the unpatched app and pass on yours**. That's the pattern the public suite is
built around, and it's how you prove your fix actually closed the hole rather than just
changing the error message.

Two worked examples:

## Example 1 — after fixing the SQL injection in ticket search (A05)

```php
public function test_search_does_not_leak_tickets_via_sql_injection(): void
{
    $agent = User::factory()->agent()->create();
    Ticket::factory()->create(['subject' => 'Alpha']);
    Ticket::factory()->create(['subject' => 'Beta']);

    $response = $this->actingAs($agent)->get("/tickets?q=" . urlencode("nonexistent' OR '1'='1"));

    // Once search uses parameter bindings, this payload is treated as a literal string
    // and matches nothing — on the vulnerable app it currently returns every ticket.
    $response->assertDontSee('Alpha');
    $response->assertDontSee('Beta');
}
```

## Example 2 — after fixing the mass-assignment privilege escalation (A01b)

```php
public function test_updating_your_profile_cannot_change_your_role(): void
{
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)->patch('/profile', [
        'name' => $customer->name,
        'email' => $customer->email,
        'role' => 'admin',
    ]);

    // On the vulnerable app this passes role=admin straight to User::update() and the
    // customer becomes an admin. Once `role` is removed from what profile updates can
    // touch, it should stay 'customer' no matter what the request body says.
    $this->assertSame('customer', $customer->fresh()->role);
}
```

Put your own version of these in a suitably-named test file (e.g. `tests/Feature/Security/
TicketSearchTest.php`) as you go — one per category is enough to prove the fix, you don't
need to reproduce the whole hidden suite.
