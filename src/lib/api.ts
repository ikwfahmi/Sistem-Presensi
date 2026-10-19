import 'server-only';
import { supabase } from './supabase/server';
import type { NextRequest } from 'next/server';
export function sameOrigin(request: NextRequest) {
  const origin = request.headers.get('origin');
  return !!origin && origin === new URL(request.url).origin;
}
export async function apiUser() {
  const db = await supabase();
  const { data: { user } } = await db.auth.getUser();
  if (!user) return null;
  const { data: profile } = await db.from('profiles').select('role').eq('id', user.id).single();
  if (!profile) return null;
  return { db, user, role: profile.role as string };
}
