<?php

namespace App\Services;

use App\Models\CashbackCampaignWinner;
use App\Models\CashbackRedemption;
use App\Models\CampaignUserRanking;
use App\Models\GuideUsage;
use App\Models\Invoice;
use App\Models\InvoiceAudit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class UserCleanupService
{
    public function __construct(
        protected \App\Services\Invoice\InvoiceAdminService $invoiceAdminService
    ) {}

    public function preview(User $user): array
    {
        $invoiceIds = Invoice::query()
            ->where('user_id', $user->id)
            ->pluck('id');

        $invoiceCount = $invoiceIds->count();

        $invoiceItemsCount = $invoiceIds->isEmpty()
            ? 0
            : DB::table('invoice_items')
                ->whereIn('invoice_id', $invoiceIds)
                ->count();

        $invoiceTransactionsCount = $invoiceIds->isEmpty()
            ? 0
            : DB::table('cashback_transactions')
                ->whereIn('invoice_id', $invoiceIds)
                ->count();

        $directTransactionsCount = DB::table('cashback_transactions')
            ->where('user_id', $user->id)
            ->whereNull('invoice_id')
            ->count();

        $rankingCount = CampaignUserRanking::query()
            ->where('user_id', $user->id)
            ->count();

        $winnerCount = CashbackCampaignWinner::query()
            ->where('user_id', $user->id)
            ->count();

        $redemptionCount = CashbackRedemption::query()
            ->where('user_id', $user->id)
            ->count();

        $guideUsageCount = GuideUsage::query()
            ->where('user_id', $user->id)
            ->count();

        $passwordResetCount = Schema::hasTable('password_resets')
            ? DB::table('password_resets')
                ->where('user_id', $user->id)
                ->count()
            : 0;

        $sessionCount = Schema::hasTable('sessions')
            ? DB::table('sessions')
                ->where('user_id', $user->id)
                ->count()
            : 0;

        $tokenCount = Schema::hasTable('personal_access_tokens')
            ? DB::table('personal_access_tokens')
                ->where('tokenable_type', User::class)
                ->where('tokenable_id', $user->id)
                ->count()
            : 0;

        $oldPasswordResetCount = 0;

        if (
            $user->email &&
            Schema::hasTable('password_reset_tokens')
        ) {
            $oldPasswordResetCount = DB::table('password_reset_tokens')
                ->where('email', $user->email)
                ->count();
        }

        $auditAsAdminCount = InvoiceAudit::query()
            ->where('admin_user_id', $user->id)
            ->count();

        $auditsOnOtherUsersInvoices = $invoiceIds->isEmpty()
            ? InvoiceAudit::query()
                ->where('admin_user_id', $user->id)
                ->exists()
            : InvoiceAudit::query()
                ->where('admin_user_id', $user->id)
                ->whereNotIn('invoice_id', $invoiceIds)
                ->exists();

        return [
            'usuario' => [
                'id' => $user->id,
                'nombre' => trim(
                    $user->first_name . ' ' . $user->last_name
                ),
                'identificacion' => $user->identification,
                'username' => $user->username,
                'email' => $user->email,
                'rol' => $user->role?->name,
                'activo' => (bool) $user->is_active,
            ],

            'datos' => [
                'facturas' => $invoiceCount,
                'items_facturas' => $invoiceItemsCount,
                'movimientos_facturas' => $invoiceTransactionsCount,
                'movimientos_sin_factura' => $directTransactionsCount,
                'rankings' => $rankingCount,
                'ganadores' => $winnerCount,
                'redenciones' => $redemptionCount,
                'usos_guia' => $guideUsageCount,
                'recuperaciones_password' => $passwordResetCount,
                'sesiones' => $sessionCount,
                'tokens' => $tokenCount,
                'password_reset_legacy' => $oldPasswordResetCount,
                'auditorias_como_admin' => $auditAsAdminCount,
            ],

            'saldos' => [
                'cashback_total' => (float) $user->cashback_total,
                'cashback_claimed' => (float) $user->cashback_claimed,
                'cashback_available' => (float) $user->cashback_available,
            ],

            'riesgos' => [
                'tiene_auditorias_sobre_otros_usuarios' =>
                    $auditsOnOtherUsersInvoices,
            ],
        ];
    }

    public function cleanTestData(User $user): array
    {
        return DB::transaction(function () use ($user) {

            $user = User::query()
                ->with('role')
                ->lockForUpdate()
                ->findOrFail($user->id);

            $this->ensureCanClean($user);

            $summary = $this->preview($user);

            $affectedCampaignIds =
                $this->getAffectedCampaignIds($user);

            $this->deleteTransactionalData($user);

            $user->update([
                'cashback_total' => 0,
                'cashback_claimed' => 0,
                'cashback_available' => 0,
            ]);

            $this->rebuildAffectedCampaigns(
                $affectedCampaignIds
            );

            return $summary;
        });
    }

    public function deleteUser(User $user): array
    {
        return DB::transaction(function () use ($user) {

            $user = User::query()
                ->with('role')
                ->lockForUpdate()
                ->findOrFail($user->id);

            $this->ensureCanDelete($user);

            $summary = $this->preview($user);

            $affectedCampaignIds =
                $this->getAffectedCampaignIds($user);

            $this->deleteTransactionalData($user);

            $this->deleteUserFiles($user);

            $user->delete();

            $this->rebuildAffectedCampaigns(
                $affectedCampaignIds
            );

            return $summary;
        });
    }

    protected function getAffectedCampaignIds(
        User $user
    ) {
        return Invoice::query()
            ->where('user_id', $user->id)
            ->whereNotNull('cashback_campaign_id')
            ->pluck('cashback_campaign_id')
            ->unique()
            ->values();
    }

    protected function rebuildAffectedCampaigns(
        $campaignIds
    ): void {

        foreach ($campaignIds as $campaignId) {

            $this->invoiceAdminService
                ->rebuildRankingsForCampaign(
                    (int) $campaignId
                );
        }
    }

    protected function deleteTransactionalData(User $user): void
    {
        $invoiceIds = Invoice::query()
            ->where('user_id', $user->id)
            ->pluck('id');

        if ($invoiceIds->isNotEmpty()) {

            $this->deleteInvoiceFiles($invoiceIds);

            InvoiceAudit::query()
                ->whereIn('invoice_id', $invoiceIds)
                ->delete();

            DB::table('invoice_items')
                ->whereIn('invoice_id', $invoiceIds)
                ->delete();

            DB::table('cashback_transactions')
                ->whereIn('invoice_id', $invoiceIds)
                ->delete();

            Invoice::query()
                ->whereIn('id', $invoiceIds)
                ->delete();
        }

        DB::table('cashback_transactions')
            ->where('user_id', $user->id)
            ->delete();

        CampaignUserRanking::query()
            ->where('user_id', $user->id)
            ->delete();

        CashbackCampaignWinner::query()
            ->where('user_id', $user->id)
            ->delete();

        CashbackRedemption::query()
            ->where('user_id', $user->id)
            ->delete();

        GuideUsage::query()
            ->where('user_id', $user->id)
            ->delete();

        if (Schema::hasTable('password_resets')) {
            DB::table('password_resets')
                ->where('user_id', $user->id)
                ->delete();
        }

        if (Schema::hasTable('sessions')) {
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();
        }

        if (Schema::hasTable('personal_access_tokens')) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', User::class)
                ->where('tokenable_id', $user->id)
                ->delete();
        }

        if (
            $user->email &&
            Schema::hasTable('password_reset_tokens')
        ) {
            DB::table('password_reset_tokens')
                ->where('email', $user->email)
                ->delete();
        }
    }

    protected function deleteInvoiceFiles($invoiceIds): void
    {
        $paths = DB::table('invoices')
            ->whereIn('id', $invoiceIds)
            ->whereNotNull('foto_factura')
            ->pluck('foto_factura');

        foreach ($paths as $path) {

            if (!$path) {
                continue;
            }

            $cleanPath = ltrim(
                preg_replace(
                    '#^storage/#',
                    '',
                    $path
                ),
                '/'
            );

            if (
                Storage::disk('public')
                    ->exists($cleanPath)
            ) {
                Storage::disk('public')
                    ->delete($cleanPath);
            }
        }
    }

    protected function deleteUserFiles(User $user): void
    {
        if (!$user->photo) {
            return;
        }

        $cleanPath = ltrim(
    preg_replace(
        '#^storage/#',
        '',
        $user->photo
    ),
    '/'
);

        if (
            Storage::disk('public')
                ->exists($cleanPath)
        ) {
            Storage::disk('public')
                ->delete($cleanPath);
        }
    }

    protected function ensureCanClean(User $user): void
    {
        if ($user->role?->name === 'Administrator') {
            throw new RuntimeException(
                'Los usuarios con rol Administrator no pueden limpiar sus datos.'
            );
        }
    }

    protected function ensureCanDelete(User $user): void
    {
        if ($user->role?->name === 'Administrator') {
            throw new RuntimeException(
                'Los usuarios con rol Administrator no pueden eliminarse.'
            );
        }
    }
}