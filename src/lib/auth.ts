import 'server-only';
import { redirect } from 'next/navigation';
import { supabase } from './supabase/server';
import type { Profile, Role } from './types';

export async function requireUser(roles?: Role[]) {
  if (!process.env.NEXT_PUBLIC_SUPABASE_URL || !process.env.NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY) redirect('/login');
  const db = await supabase();
  const { data: { user }, error } = await db.auth.getUser();
  if (error || !user) redirect('/login');
  const { data: profile } = await db.from('profiles').select('id,name,identifier,role').eq('id', user.id).single();
  if (!profile) redirect('/login?error=Profil+belum+disiapkan.+Hubungi+admin.');
  if (roles && !roles.includes(profile.role)) redirect('/dashboard?error=Akses+ditolak');
  return { db, user, profile: profile as Profile };
}
