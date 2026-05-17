import React, { useEffect, useMemo } from 'react';
import ContentContainer from '@/components/elements/ContentContainer';
import { CSSTransition } from 'react-transition-group';
import tw from 'twin.macro';
import FlashMessageRender from '@/components/FlashMessageRender';

export interface PageContentBlockProps {
    title?: string;
    className?: string;
    showFlashKey?: string;
}

const PageContentBlock: React.FC<PageContentBlockProps> = ({ title, showFlashKey, className, children }) => {
    useEffect(() => {
        if (title) {
            document.title = title;
        }
    }, [title]);

    const githubUrl = String.fromCharCode(
        104, 116, 116, 112, 115, 58, 47, 47,
        103, 105, 116, 104, 117, 98, 46, 99, 111, 109, 47,
        66, 101, 97, 115, 116, 77, 97, 114, 107, 52, 52, 49, 47,
        112, 116, 101, 114, 111, 100, 97, 99, 116, 121, 108
    );

    const githubName = String.fromCharCode(
        64, 66, 101, 97, 115, 116, 77, 97, 114, 107, 52, 52, 49
    );

    return (
        <CSSTransition timeout={150} classNames={'fade'} appear in>
            <>
                <ContentContainer css={tw`my-4 sm:my-10`} className={className}>
                    {showFlashKey && <FlashMessageRender byKey={showFlashKey} css={tw`mb-4`} />}
                    {children}
                </ContentContainer>
                <ContentContainer css={tw`mb-4`}>
                    <p css={tw`text-center text-neutral-500 text-xs`}>
                        <a
                            rel={'noopener nofollow noreferrer'}
                            href={'https://pterodactyl.io'}
                            target={'_blank'}
                            css={tw`no-underline text-neutral-500 hover:text-neutral-300`}
                        >
                            Pterodactyl&reg;
                        </a>
                        &nbsp;&copy; 2015 - {new Date().getFullYear()}
                        <span css={tw`mx-1`}>·</span>
                        <a
                            rel={'noopener noreferrer'}
                            href={githubUrl}
                            target={'_blank'}
                            css={tw`no-underline text-neutral-500 hover:text-neutral-300`}
                        >
                            {githubName}
                        </a>
                    </p>
                </ContentContainer>
            </>
        </CSSTransition>
    );
};

export default PageContentBlock;
