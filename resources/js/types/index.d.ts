export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
}

export interface Game {
  id: number;
  api_id: number | null;
  title: string;
  thumbnail: string | null;
  short_description: string | null;
  game_url: string | null;
  genre: string | null;
  platform: string | null;
  publisher: string | null;
  developer: string | null;
  release_date: string | null;
  freetogame_profile_url: string | null;
  created_at: string;
  updated_at: string;
}

export interface PaginatedData<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

export type PageProps<
  T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
  auth: {
    user: User;
  };
  flash?: {
    success?: string;
    error?: string;
  };
};
