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
                    message: 'Ваш сервер начал процесс переустановки.',
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
        <TitledGreyBox title={'Переустановить Сервер'} css={tw`relative`}>
            <Dialog.Confirm
                open={modalVisible}
                title={'Подтвердите переустановку сервера'}
                confirm={'Да, переустановить сервер'}
                onClose={() => setModalVisible(false)}
                onConfirmed={reinstall}
            >
                Ваш сервер будет остановлен, и некоторые файлы могут быть удалены или изменены в процессе. 
                Вы уверены, что хотите продолжить?
            </Dialog.Confirm>
            <p css={tw`text-sm`}>
                Переустановка вашего сервера остановит его, а затем повторно запустит скрипт установки, который изначально его настроил.&nbsp;
                <strong css={tw`font-medium`}>
                    Некоторые файлы могут быть удалены или изменены в процессе, пожалуйста, сделайте резервную копию ваших данных перед 
                    продолжением.
                </strong>
            </p>
            <div css={tw`mt-6 text-right`}>
                <Button.Danger variant={Button.Variants.Secondary} onClick={() => setModalVisible(true)}>
                    Переустановить Сервер
                </Button.Danger>
            </div>
        </TitledGreyBox>
    );
};
