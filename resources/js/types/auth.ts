import type { User } from './user';

export type Auth = {
    user: User;
    has_author_profile: boolean;
    has_beta_reader_profile: boolean;
};

export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
