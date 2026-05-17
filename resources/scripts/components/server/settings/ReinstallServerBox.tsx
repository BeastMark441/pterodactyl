import React, { useEffect, useState } from 'react';
import { ServerContext } from '@/state/server';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import reinstallServer from '@/api/server/reinstallServer';
import { Actions, useStoreActions } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { httpErrorToHuman } from '@/api/http';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import { Dialog } from '@/components/elements/dialog';

export default () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const [modalVisible, setModalVisible] = useState(false);
    const { addFlash, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const reinstall = () => {
        clearFlashes('settings');
        reinstallServer(uuid)
            .then(() => {
                addFlash({
                    key: 'settings',
                    type: 'success',
                    message: 'На вашем сервере начался процесс переустановки.',
                });
            })
            .catch((error) => {
                console.error(error);

                addFlash({ key: 'settings', type: 'error', message: httpErrorToHuman(error) });
            })
            .then(() => setModalVisible(false));
    };

    useEffect(() => {
        clearFlashes();
    }, []);

    return (
        <TitledGreyBox title={'Переустановка сервера'} css={tw`relative`}>
            <Dialog.Confirm
                open={modalVisible}
                title={'Подтвердите переустановку сервера'}
                confirm={'Да, переустановить сервер'}
                onClose={() => setModalVisible(false)}
                onConfirmed={reinstall}
            >
                Ваш сервер будет остановлен, и в ходе этого процесса некоторые файлы могут быть удалены или изменены, вы уверены?
                вы хотите продолжить?
            </Dialog.Confirm>
            <p css={tw`text-sm`}>
                Переустановка вашего сервера остановит его, а затем повторно запустит установочный скрипт, который изначально его настроил.&nbsp;
                <strong css={tw`font-medium`}>
                    Во время этого процесса некоторые файлы могут быть удалены или изменены, пожалуйста, создайте резервную копию ваших данных, прежде чем продолжить.
                </strong>
            </p>
            <div css={tw`mt-6 text-right`}>
                <Button.Danger variant={Button.Variants.Secondary} onClick={() => setModalVisible(true)}>
                    Переустановить сервер
                </Button.Danger>
            </div>
        </TitledGreyBox>
    );
};
