import { bytesToString, ip, mbToBytes } from '@/lib/formatters';

describe('@/lib/formatters.ts', function () {
    describe('mbToBytes()', function () {
        it('should convert from MB to Bytes', function () {
            expect(mbToBytes(1)).toBe(1_048_576);
            expect(mbToBytes(0)).toBe(0);
            expect(mbToBytes(0.1)).toBe(104_857);
            expect(mbToBytes(0.001)).toBe(1_048);
            expect(mbToBytes(1024)).toBe(1_073_741_824);
        });
    });

    describe('bytesToString()', function () {
        it.each([
            [0, '0 байт'],
            [0.5, '0 байт'],
            [0.9, '0 байт'],
            [100, '100 байт'],
            [100.25, '100.25 байт'],
            [100.998, '101 байт'],
            [512, '512 байт'],
            [1000, '1000 байт'],
            [1024, '1 Кбайт'],
            [5068, '4.95 Кбайт'],
            [10_000, '9.77 Кбайт'],
            [10_240, '10 Кбайт'],
            [11_864, '11.59 Кбайт'],
            [1_000_000, '976.56 Кбайт'],
            [1_024_000, '1000 Кбайт'],
            [1_025_000, '1000.98 Кбайт'],
            [1_048_576, '1 Мбайт'],
            [1_356_000, '1.29 Мбайт'],
            [1_000_000_000, '953.67 Мбайт'],
            [1_070_000_100, '1020.43 Мбайт'],
            [1_073_741_824, '1 Гбайт'],
            [1_678_342_000, '1.56 Гбайт'],
            [1_000_000_000_000, '931.32 Гбайт'],
            [1_099_511_627_776, '1 Тбайт'],
        ])('should format %d bytes as "%s"', function (input, output) {
            expect(bytesToString(input)).toBe(output);
        });
    });

    describe('ip()', function () {
        it('should format an IPv4 address', function () {
            expect(ip('127.0.0.1')).toBe('127.0.0.1');
        });

        it('should format an IPv6 address', function () {
            expect(ip(':::1')).toBe('[:::1]');
            expect(ip('2001:db8::')).toBe('[2001:db8::]');
        });

        it('should handle random inputs', function () {
            expect(ip('1')).toBe('1');
            expect(ip('foobar')).toBe('foobar');
            expect(ip('127.0.0.1:25565')).toBe('[127.0.0.1:25565]');
        });
    });
});
