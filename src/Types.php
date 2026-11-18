<?php

declare(strict_types=1);

namespace PonchoPay;

/**
 * @phpstan-type PaymentInit array{
 *   metadata: string,
 *   urn: string,
 *   amount: int,
 *   email: string,
 *   note?: string,
 *   expiry?: \DateTimeInterface|string,
 *   constraints?: array{
 *     minimum_card_amount?: int
 *   },
 *   line_items?: list<array{
 *     description: string,
 *     amount: int,
 *     quantity: int
 *   }>
 * }
 * @phpstan-type SubscriptionInit array{
 *   urn: string,
 *   amount: int,
 *   metadata: string,
 *   note?: string,
 *   email: string,
 *   repetition: array{granularity: 'day', period: int}|array{granularity: 'month', period: int, day: int}|array{granularity: 'week', period: int, weekdays: string[]}|array{granularity: 'year', period: int},
 *   ending?: array{condition: 'date', date: \DateTimeInterface|string}|array{condition: 'never'}|array{condition: 'occurrences', occurrences: int},
 *   additional_one_time_payment?: array{
 *     metadata: string,
 *     amount: int,
 *     note?: string,
 *     expiry?: \DateTimeInterface|string,
 *     constraints?: array{minimum_card_amount?: int}
 *   }
 * }
 * @phpstan-type JWTPayload array{
 *   urn: string,
 *   email: string
 * }
 * @phpstan-type PaymentMethodUpdate array{
 *   urn: string,
 *   email: string,
 *   type: 'card'|'childcare-voucher'|'tax-free-childcare',
 *   amount: int,
 *   voucher_provider?: string
 * }
 * @phpstan-type PaymentMethodRefund JWTPayload & array{
 *   urn: string,
 *   email: string,
 *   amount: integer
 * }
 * @phpstan-type LocationStatus object{
 *   verification_status: bool,
 *   card_payments_enabled: bool,
 *   childcare_voucher_payments_enabled: bool,
 *   tax_free_childcare_payments_enabled: bool
 * }
 * @phpstan-type PaymentWebhook object{
 *   id: string,
 *   date: string,
 *   event: string,
 *   payment: object{
 *     id: string,
 *     reference: string,
 *     amount: integer,
 *     refunded_amount: integer,
 *     refunded_total: integer,
 *     method: string,
 *     payment_methods: list<object{
 *       id: string,
 *       type: string,
 *       variant: string,
 *       amount: integer,
 *       refunded_amount: integer,
 *       refunded_total: integer
 *     }>
 *   },
 *   request: object{
 *     metadata: string
 *   }
 * }
 */
final class Types {}
