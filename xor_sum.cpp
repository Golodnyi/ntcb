#include <stdio.h>

unsigned char xor_sum (unsigned char *buffer, unsigned int length) {
    unsigned char temp_sum = 0;
    while (length-- > 0) {
        temp_sum ^= *buffer++;
    }

    return (temp_sum);
}

int main() {
    unsigned char buffer[] = "*>S:865905024676581";
    unsigned int length = 19;
    xor_sum(buffer, length);
    return 0;
}