<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('id', 'desc')->get();
        return view('pages.roles.index', compact('roles'));
    }

    public function addRole(Request $request)
    {
        Role::create([
            'role_name' => $request->role_name,
            'superadmin' => $request->superadmin ?? 'off',
            'manage_user' => $request->manage_user ?? 'off',
            'manage_categories' => $request->manage_categories ?? 'off',
            'manage_products' => $request->manage_products ?? 'off',
            'manage_locations' => $request->manage_locations ?? 'off',
            'manage_supplier' => $request->manage_supplier ?? 'off',
            'manage_opening_quantity' => $request->manage_opening_quantity ?? 'off',
            'manage_goods_receipt' => $request->manage_goods_receipt ?? 'off',
            'manage_inventory_transfer' => $request->manage_inventory_transfer ?? 'off',
            'manage_inventory_adjustment' => $request->manage_inventory_adjustment ?? 'off',
            'manage_patients' => $request->manage_patients ?? 'off',
            'manage_treatment_consumption' => $request->manage_treatment_consumption ?? 'off',
            'manage_product_sales' => $request->manage_product_sales ?? 'off',
            'view_reports' => $request->view_reports ?? 'off',
        ]);

        return back()->with('success', 'New Role Added.');
    }

    public function editRole(Request $request, $id)
    {
        Role::where('id', $id)->update([
            'role_name' => $request->role_name,
            'superadmin' => $request->superadmin ?? 'off',
            'manage_user' => $request->manage_user ?? 'off',
            'manage_categories' => $request->manage_categories ?? 'off',
            'manage_products' => $request->manage_products ?? 'off',
            'manage_locations' => $request->manage_locations ?? 'off',
            'manage_supplier' => $request->manage_supplier ?? 'off',
            'manage_opening_quantity' => $request->manage_opening_quantity ?? 'off',
            'manage_goods_receipt' => $request->manage_goods_receipt ?? 'off',
            'manage_inventory_transfer' => $request->manage_inventory_transfer ?? 'off',
            'manage_inventory_adjustment' => $request->manage_inventory_adjustment ?? 'off',
            'manage_patients' => $request->manage_patients ?? 'off',
            'manage_treatment_consumption' => $request->manage_treatment_consumption ?? 'off',
            'manage_product_sales' => $request->manage_product_sales ?? 'off',
            'view_reports' => $request->view_reports ?? 'off',
        ]);

        return back()->with('success', 'Role Updated.');
    }

    public function deleteRole($id)
    {
        Role::where('id', $id)->delete();
        return back()->with('success', 'Role Deleted.');
    }

    public function setRole(Request $request, $id)
    {
        User::where('id', $id)->update(['role_id' => $request->role]);
        return back()->with('success', 'Set Role Succeed.');
    }
}